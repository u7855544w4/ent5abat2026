"""
نظام متابعةElection比利2026 - Backend API
FastAPI backend for municipality election tracking system
"""

from datetime import datetime
from enum import Enum
from typing import Optional, List, Dict, Any
from fastapi import FastAPI, HTTPException, UploadFile, File, Depends, Form, Query
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
from fastapi.responses import HTMLResponse
from fastapi.security import HTTPBasic, HTTPBasicCredentials
from pydantic import BaseModel
from sqlalchemy import create_engine, Column, Integer, String, Text, DateTime, Enum as SQLEnum, ForeignKey, func
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, Session, relationship
import openpyxl
import io
import hashlib

# Security
security = HTTPBasic()

# ============================================
# Database Setup
# ============================================
DATABASE_URL = "sqlite:///./election.db"
engine = create_engine(DATABASE_URL, connect_args={"check_same_thread": False})
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()


# ============================================
# Enums
# ============================================
class ElectorStatus(str, Enum):
    PENDING = "pending"  # في الانتظار
    GUARANTEED = "guaranteed"  # مضمون (انخب قائمتنا)
    UNGUARANTEED = "unguaranteed"  # غير مضمون (انخب قائمة أخرى)
    NOT_VOTED = "not_voted"  # لم ينخب


class UserRole(str, Enum):
    ADMIN = "admin"
    COMMITTEE_MEMBER = "committee_member"


# ============================================
# Database Models
# ============================================
class User(Base):
    __tablename__ = "users"
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(255), nullable=False)
    email = Column(String(255), unique=True, nullable=False)
    password_hash = Column(String(255), nullable=False)
    role = Column(SQLEnum(UserRole), default=UserRole.COMMITTEE_MEMBER)
    family_name = Column(String(255), nullable=True)  # العائلة المشرف عليها
    electoral_center = Column(String(255), nullable=True)  # المركز electionي المشرف عليه
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    assigned_electors = relationship("Elector", back_populates="assigned_user")
    follow_ups = relationship("FollowUp", back_populates="user")


class Elector(Base):
    __tablename__ = "electors"
    
    id = Column(Integer, primary_key=True, index=True)
    full_name = Column(String(255), nullable=False)
    family_name = Column(String(255), nullable=False)
    electoral_code = Column(String(50), unique=True, nullable=False)
    electoral_center = Column(String(255), nullable=False)
    status = Column(SQLEnum(ElectorStatus), default=ElectorStatus.PENDING)
    assigned_to = Column(Integer, ForeignKey("users.id"), nullable=True)
    notes = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    assigned_user = relationship("User", back_populates="assigned_electors")
    follow_ups = relationship("FollowUp", back_populates="elector", cascade="all, delete-orphan")


class FollowUp(Base):
    __tablename__ = "follow_ups"
    
    id = Column(Integer, primary_key=True, index=True)
    elector_id = Column(Integer, ForeignKey("electors.id"), nullable=False)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    status = Column(SQLEnum(ElectorStatus), nullable=False)
    notes = Column(Text, nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    elector = relationship("Elector", back_populates="follow_ups")
    user = relationship("User", back_populates="follow_ups")


# Create tables
Base.metadata.create_all(bind=engine)


# ============================================
# Pydantic Models
# ============================================
class UserCreate(BaseModel):
    name: str
    email: str
    password: str
    role: UserRole = UserRole.COMMITTEE_MEMBER
    family_name: Optional[str] = None
    electoral_center: Optional[str] = None


class UserResponse(BaseModel):
    id: int
    name: str
    email: str
    role: UserRole
    family_name: Optional[str]
    electoral_center: Optional[str]
    created_at: datetime
    
    class Config:
        from_attributes = True


class UserUpdate(BaseModel):
    name: Optional[str] = None
    email: Optional[str] = None
    password: Optional[str] = None
    family_name: Optional[str] = None
    electoral_center: Optional[str] = None


class ElectorCreate(BaseModel):
    full_name: str
    family_name: str
    electoral_code: str
    electoral_center: str


class ElectorUpdate(BaseModel):
    full_name: Optional[str] = None
    family_name: Optional[str] = None
    electoral_code: Optional[str] = None
    electoral_center: Optional[str] = None
    status: Optional[ElectorStatus] = None
    assigned_to: Optional[int] = None
    notes: Optional[str] = None


class ElectorResponse(BaseModel):
    id: int
    full_name: str
    family_name: str
    electoral_code: str
    electoral_center: str
    status: ElectorStatus
    assigned_to: Optional[int]
    notes: Optional[str]
    created_at: datetime
    updated_at: datetime
    
    class Config:
        from_attributes = True


class FollowUpCreate(BaseModel):
    elector_id: int
    user_id: int
    status: ElectorStatus
    notes: Optional[str] = None


class FollowUpResponse(BaseModel):
    id: int
    elector_id: int
    user_id: int
    status: ElectorStatus
    notes: Optional[str]
    created_at: datetime
    
    class Config:
        from_attributes = True


class DashboardStats(BaseModel):
    total_electors: int
    guaranteed: int
    ununuranted: int
    not_voted: int
    pending: int
    guaranteed_percentage: float
    family_breakdown: List[dict]
    center_breakdown: List[dict]


class ImportResult(BaseModel):
    imported: int
    updated: int
    errors: List[str]


# ============================================
# Dependencies
# ============================================
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


def create_default_user(db):
    """Create default admin user"""
    admin = db.query(User).filter(User.email == "admin@election.dz").first()
    if not admin:
        password_hash = hashlib.sha256("admin123".encode()).hexdigest()
        admin = User(
            name="مدير النظام",
            email="admin@election.dz",
            password_hash=password_hash,
            role=UserRole.ADMIN,
            family_name=None
        )
        db.add(admin)
        db.commit()
    return admin


def get_current_user(credentials: HTTPBasicCredentials = Depends(security), db: Session = Depends(get_db)):
    """Authenticate user with username/password"""
    user = db.query(User).filter(User.email == credentials.username).first()
    if not user:
        raise HTTPException(status_code=401, detail="البريد الإلكتروني غير صحيح")
    
    password_hash = hashlib.sha256(credentials.password.encode()).hexdigest()
    if user.password_hash != password_hash:
        raise HTTPException(status_code=401, detail="كلمة المرور غير صحيحة")
    
    return user


def get_current_admin(admin: User = Depends(get_current_user)):
    """Require admin role"""
    if admin.role != UserRole.ADMIN:
        raise HTTPException(status_code=403, detail="ليس لديك صلاحية للدخول")
    return admin


# ============================================
# API Routes
# ============================================
app = FastAPI(title="نظام متابعة انتخبات البلدية 2026")

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Mount static files
import os
os.makedirs("static", exist_ok=True)
app.mount("/static", StaticFiles(directory="static"), name="static")


@app.get("/")
async def root():
    with open("static/index.html", "r", encoding="utf-8") as f:
        return HTMLResponse(f.read())


# Login endpoint
@app.post("/api/login")
async def login(request: dict, db: Session = Depends(get_db)):
    """Login with email and password"""
    email = request.get("email")
    password = request.get("password")
    
    if not email or not password:
        raise HTTPException(status_code=400, detail="البريد وكلمة المرور مطلوبان")
    
    user = db.query(User).filter(User.email == email).first()
    if not user:
        raise HTTPException(status_code=401, detail="البريد الإلكتروني غير صحيح")
    
    password_hash = hashlib.sha256(password.encode()).hexdigest()
    if user.password_hash != password_hash:
        raise HTTPException(status_code=401, detail="كلمة المرور غير صحيحة")
    
    return {
        "user": {
            "id": user.id,
            "name": user.name,
            "email": user.email,
            "role": user.role.value if hasattr(user.role, 'value') else user.role,
            "family_name": user.family_name,
            "electoral_center": user.electoral_center
        }
    }


@app.post("/api/logout")
async def logout():
    """Logout - clear session"""
    return {"message": "Logged out successfully"}


@app.get("/api/me")
async def get_me(user: User = Depends(get_current_user)):
    """Get current user info"""
    return {
        "id": user.id,
        "name": user.name,
        "email": user.email,
        "role": user.role.value if hasattr(user.role, 'value') else user.role,
        "family_name": user.family_name,
        "electoral_center": user.electoral_center
    }


@app.get("/api/dashboard", response_model=DashboardStats)
async def get_dashboard(
    db: Session = Depends(get_db),
    family_name: Optional[str] = None,
    electoral_center: Optional[str] = None
):
    """Get dashboard statistics with optional filters"""
    query = db.query(Elector)
    if family_name:
        query = query.filter(Elector.family_name == family_name)
    if electoral_center:
        query = query.filter(Elector.electoral_center == electoral_center)
    
    total = query.count()
    guaranteed = query.filter(Elector.status == ElectorStatus.GUARANTEED).count()
    ununuranted = query.filter(Elector.status == ElectorStatus.UNGUARANTEED).count()
    not_voted = query.filter(Elector.status == ElectorStatus.NOT_VOTED).count()
    pending = query.filter(Elector.status == ElectorStatus.PENDING).count()
    
    # Family breakdown
    family_stats = db.query(
        Elector.family_name,
        Elector.status,
        func.count(Elector.id).label('count')
    ).group_by(Elector.family_name, Elector.status).all()
    
    family_map = {}
    for family, status, count in family_stats:
        if family not in family_map:
            family_map[family] = {"family_name": family, "guaranteed": 0, "unguaranteed": 0, "not_voted": 0, "pending": 0, "total": 0}
        family_map[family][status] = count
        family_map[family]["total"] += count
    
    # Center breakdown
    center_stats = db.query(
        Elector.electoral_center,
        Elector.status,
        func.count(Elector.id).label('count')
    ).group_by(Elector.electoral_center, Elector.status).all()
    
    center_map = {}
    for center, status, count in center_stats:
        if center not in center_map:
            center_map[center] = {"center": center, "guaranteed": 0, "unguaranteed": 0, "not_voted": 0, "pending": 0, "total": 0}
        center_map[center][status] = count
        center_map[center]["total"] += count
    
    return DashboardStats(
        total_electors=total,
        guaranteed=guaranteed,
        ununuranted=ununuranted,
        not_voted=not_voted,
        pending=pending,
        guaranteed_percentage=round(guaranteed / total * 100, 1) if total > 0 else 0,
        family_breakdown=list(family_map.values()),
        center_breakdown=list(center_map.values())
    )


@app.get("/api/meta")
async def get_meta(db: Session = Depends(get_db)):
    """Get all families and centers for dropdowns"""
    families = db.query(Elector.family_name).distinct().all()
    centers = db.query(Elector.electoral_center).distinct().all()
    return {
        "families": [f[0] for f in families if f[0]],
        "centers": [c[0] for c in centers if c[0]]
    }


@app.get("/api/electors", response_model=List[ElectorResponse])
async def get_electors(
    db: Session = Depends(get_db),
    family_name: Optional[str] = None,
    center: Optional[str] = None,
    status: Optional[ElectorStatus] = None,
    assigned_to: Optional[int] = None,
    search: Optional[str] = None,
    skip: int = 0,
    limit: int = 100
):
    """Get list of electors with filters"""
    query = db.query(Elector)
    
    if family_name:
        query = query.filter(Elector.family_name == family_name)
    if center:
        query = query.filter(Elector.electoral_center == center)
    if status:
        query = query.filter(Elector.status == status)
    if assigned_to:
        query = query.filter(Elector.assigned_to == assigned_to)
    if search:
        query = query.filter(
            (Elector.full_name.contains(search)) | 
            (Elector.electoral_code.contains(search))
        )
    
    results = query.offset(skip).limit(limit).all()
    return results


@app.get("/api/me/electors")
async def get_my_electors(user: User = Depends(get_current_user), db: Session = Depends(get_db)):
    """Get electors assigned to current user (for committee members)"""
    if user.role == UserRole.admin:
        return []
    
    electors = db.query(Elector).filter(Elector.assigned_to == user.id).all()
    return electors


@app.get("/api/electors/{elector_id}", response_model=ElectorResponse)
async def get_elector(elector_id: int, db: Session = Depends(get_db)):
    """Get single elector"""
    elector = db.query(Elector).filter(Elector.id == elector_id).first()
    if not elector:
        raise HTTPException(status_code=404, detail="الناخب غير موجود")
    return elector


@app.post("/api/electors", response_model=ElectorResponse)
async def create_elector(elector: ElectorCreate, db: Session = Depends(get_db)):
    """Create new elector"""
    # Check for duplicate
    existing = db.query(Elector).filter(Elector.electoral_code == elector.electoral_code).first()
    if existing:
        raise HTTPException(status_code=400, detail="الرمز انتخابي موجود مسبقا")
    
    db_elector = Elector(**elector.dict())
    db.add(db_elector)
    db.commit()
    db.refresh(db_elector)
    return db_elector


@app.put("/api/electors/{elector_id}", response_model=ElectorResponse)
async def update_elector(elector_id: int, elector: ElectorUpdate, db: Session = Depends(get_db)):
    """Update elector"""
    db_elector = db.query(Elector).filter(Elector.id == elector_id).first()
    if not db_elector:
        raise HTTPException(status_code=404, detail="الناخب غير موجود")
    
    for key, value in elector.dict(exclude_unset=True).items():
        setattr(db_elector, key, value)
    
    db.commit()
    db.refresh(db_elector)
    return db_elector


@app.delete("/api/electors/{elector_id}")
async def delete_elector(elector_id: int, db: Session = Depends(get_db)):
    """Delete elector"""
    db_elector = db.query(Elector).filter(Elector.id == elector_id).first()
    if not db_elector:
        raise HTTPException(status_code=404, detail="الناخب غير موجود")
    
    db.delete(db_elector)
    db.commit()
    return {"message": "تم حذف الناخب بنجاح"}


@app.post("/api/electors/import", response_model=ImportResult)
async def import_electors(file: UploadFile = File(...), db: Session = Depends(get_db)):
    """Import electors from Excel file"""
    try:
        contents = await file.read()
        wb = openpyxl.load_workbook(io.BytesIO(contents))
        ws = wb.active
        
        imported = 0
        updated = 0
        errors = []
        
        # Skip header row
        for row_num, row in enumerate(ws.iter_rows(min_row=2, values_only=True), start=2):
            if not row or not any(row):
                continue
            
            try:
                full_name = str(row[0]).strip() if row[0] else ""
                family_name = str(row[1]).strip() if row[1] else ""
                electoral_code = str(row[2]).strip() if row[2] else ""
                electoral_center = str(row[3]).strip() if row[3] else ""
                
                if not full_name or not electoral_code:
                    errors.append(f"الصف {row_num}: بيانات ناقصة")
                    continue
                
                # Check existing
                existing = db.query(Elector).filter(Elector.electoral_code == electoral_code).first()
                
                if existing:
                    # Update existing
                    existing.full_name = full_name
                    existing.family_name = family_name
                    existing.electoral_center = electoral_center
                    updated += 1
                else:
                    # Create new
                    new_elector = Elector(
                        full_name=full_name,
                        family_name=family_name,
                        electoral_code=electoral_code,
                        electoral_center=electoral_center
                    )
                    db.add(new_elector)
                    imported += 1
                    
            except Exception as e:
                errors.append(f"الصف {row_num}: {str(e)}")
        
        db.commit()
        return ImportResult(imported=imported, updated=updated, errors=errors[:10])  # Limit errors
        
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"خطأ في قراءة الملف: {str(e)}")


@app.post("/api/electors/{elector_id}/status")
async def update_elector_status(
    elector_id: int,
    request: dict,
    db: Session = Depends(get_db)
):
    """Update elector voting status"""
    status = ElectorStatus(request.get("status"))
    notes = request.get("notes")
    user_id = request.get("user_id", 1)
    elector = db.query(Elector).filter(Elector.id == elector_id).first()
    if not elector:
        raise HTTPException(status_code=404, detail="الناخب غير موجود")
    
    old_status = elector.status
    elector.status = status
    if notes:
        elector.notes = notes
    
    # Create follow-up record
    follow_up = FollowUp(
        elector_id=elector_id,
        user_id=user_id,
        status=status,
        notes=notes
    )
    db.add(follow_up)
    db.commit()
    
    return {"message": "تم تحديث الحالة", "old_status": old_status, "new_status": status}


@app.post("/api/electors/{elector_id}/assign")
async def assign_elector(elector_id: int, user_id: int, db: Session = Depends(get_db)):
    """Assign elector to committee member"""
    elector = db.query(Elector).filter(Elector.id == elector_id).first()
    if not elector:
        raise HTTPException(status_code=404, detail="الناخب غير موجود")
    
    user = db.query(User).filter(User.id == user_id).first()
    if not user:
        raise HTTPException(status_code=404, detail="المستخدم غير موجود")
    
    elector.assigned_to = user_id
    db.commit()
    
    return {"message": f"تم تعيين {elector.full_name} لـ {user.name}"}


@app.post("/api/electors/assign-bulk")
async def assign_bulk_electors(
    user_id: int,
    family_name: Optional[str] = None,
    center: Optional[str] = None,
    db: Session = Depends(get_db)
):
    """Assign multiple electors to a committee member"""
    query = db.query(Elector).filter(Elector.assigned_to == None)
    
    if family_name:
        query = query.filter(Elector.family_name == family_name)
    if center:
        query = query.filter(Elector.electoral_center == center)
    
    electors = query.limit(50).all()  # Limit to 50 per batch
    
    for elector in electors:
        elector.assigned_to = user_id
    
    db.commit()
    
    return {"message": f"تم تعيين {len(electors)} ناخب"}


@app.get("/api/users", response_model=List[UserResponse])
async def get_users(db: Session = Depends(get_db)):
    """Get all users/committee members"""
    return db.query(User).all()


@app.post("/api/users", response_model=UserResponse)
async def create_user(user: UserCreate, db: Session = Depends(get_db)):
    """Create new user/committee member"""
    existing = db.query(User).filter(User.email == user.email).first()
    if existing:
        raise HTTPException(status_code=400, detail="البريد مستخدم مسبقا")
    
    import hashlib
    password_hash = hashlib.sha256(user.password.encode()).hexdigest()
    
    db_user = User(
        name=user.name,
        email=user.email,
        password_hash=password_hash,
        role=user.role,
        family_name=user.family_name,
        electoral_center=user.electoral_center
    )
    db.add(db_user)
    db.commit()
    db.refresh(db_user)
    return db_user


@app.put("/api/users/{user_id}", response_model=UserResponse)
async def update_user(user_id: int, user: UserUpdate, db: Session = Depends(get_db)):
    """Update user/committee member"""
    db_user = db.query(User).filter(User.id == user_id).first()
    if not db_user:
        raise HTTPException(status_code=404, detail="المستخدم غير موجود")
    
    if user.name:
        db_user.name = user.name
    if user.email:
        # Check if email is already taken
        existing = db.query(User).filter(User.email == user.email, User.id != user_id).first()
        if existing:
            raise HTTPException(status_code=400, detail="البريد الإلكتروني مستخدم من قبل")
        db_user.email = user.email
    if user.password:
        db_user.hashed_password = hash_password(user.password)
    if user.family_name is not None:
        db_user.family_name = user.family_name
    if user.electoral_center is not None:
        db_user.electoral_center = user.electoral_center
    
    db.commit()
    db.refresh(db_user)
    return db_user


@app.get("/api/reports/families")
async def get_family_report(db: Session = Depends(get_db)):
    """Get report by family"""
    families = db.query(Elector.family_name, func.count(Elector.id).label('total')).group_by(Elector.family_name).all()
    
    result = []
    for family, total in families:
        guaranteed = db.query(Elector).filter(Elector.family_name == family, Elector.status == ElectorStatus.GUARANTEED).count()
        ununuranted = db.query(Elector).filter(Elector.family_name == family, Elector.status == ElectorStatus.UNGUARANTEED).count()
        not_voted = db.query(Elector).filter(Elector.family_name == family, Elector.status == ElectorStatus.NOT_VOTED).count()
        pending = db.query(Elector).filter(Elector.family_name == family, Elector.status == ElectorStatus.PENDING).count()
        
        result.append({
            "family_name": family,
            "total": total,
            "guaranteed": guaranteed,
            "unguaranteed": ununuranted,
            "not_voted": not_voted,
            "pending": pending,
            "guaranteed_percentage": round(guaranteed / total * 100, 1) if total > 0 else 0
        })
    
    return result


@app.get("/api/reports/centers")
async def get_center_report(db: Session = Depends(get_db)):
    """Get report by electoral center"""
    centers = db.query(Elector.electoral_center, func.count(Elector.id).label('total')).group_by(Elector.electoral_center).all()
    
    result = []
    for center, total in centers:
        guaranteed = db.query(Elector).filter(Elector.electoral_center == center, Elector.status == ElectorStatus.GUARANTEED).count()
        ununuranted = db.query(Elector).filter(Elector.electoral_center == center, Elector.status == ElectorStatus.UNGUARANTEED).count()
        not_voted = db.query(Elector).filter(Elector.electoral_center == center, Elector.status == ElectorStatus.NOT_VOTED).count()
        pending = db.query(Elector).filter(Elector.electoral_center == center, Elector.status == ElectorStatus.PENDING).count()
        
        result.append({
            "center": center,
            "total": total,
            "guaranteed": guaranteed,
            "unguaranteed": ununuranted,
            "not_voted": not_voted,
            "pending": pending,
            "guaranteed_percentage": round(guaranteed / total * 100, 1) if total > 0 else 0
        })
    
    return result


@app.get("/api/reports/members")
async def get_member_report(db: Session = Depends(get_db)):
    """Get report by committee members"""
    users = db.query(User).filter(User.role == UserRole.COMMITTEE_MEMBER).all()
    
    result = []
    for user in users:
        assigned = db.query(Elector).filter(Elector.assigned_to == user.id).count()
        completed = db.query(Elector).filter(Elector.assigned_to == user.id, Elector.status != ElectorStatus.PENDING).count()
        
        result.append({
            "user_id": user.id,
            "user_name": user.name,
            "family_name": user.family_name,
            "assigned": assigned,
            "completed": completed,
            "pending": assigned - completed,
            "completion_percentage": round(completed / assigned * 100, 1) if assigned > 0 else 0
        })
    
    return result


@app.get("/api/followups/{elector_id}", response_model=List[FollowUpResponse])
async def get_elector_followups(elector_id: int, db: Session = Depends(get_db)):
    """Get follow-up history for elector"""
    return db.query(FollowUp).filter(FollowUp.elector_id == elector_id).order_by(FollowUp.created_at.desc()).all()


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)