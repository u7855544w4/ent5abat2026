from flask import Flask, render_template, request, redirect, url_for, flash, jsonify, send_file
import sqlite3
import pandas as pd
from datetime import datetime
import os
from io import BytesIO

app = Flask(__name__)
app.secret_key = 'election_2026_secret_key'
DB_NAME = 'election.db'

def init_db():
    conn = sqlite3.connect(DB_NAME)
    c = conn.cursor()
    
    # Families table
    c.execute('''CREATE TABLE IF NOT EXISTS families (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL
    )''')
    
    # Committees table
    c.execute('''CREATE TABLE IF NOT EXISTS committees (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT
    )''')
    
    # Voters table
    c.execute('''CREATE TABLE IF NOT EXISTS voters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        family_name TEXT,
        electoral_code TEXT,
        electoral_center TEXT,
        status TEXT DEFAULT 'pending',
        notes TEXT,
        committee_id INTEGER,
        followed_by TEXT,
        followup_date TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (committee_id) REFERENCES committees(id)
    )''')
    
    # Tasks table
    c.execute('''CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        committee_id INTEGER,
        voter_id INTEGER,
        assigned_at TEXT DEFAULT CURRENT_TIMESTAMP,
        status TEXT DEFAULT 'pending',
        FOREIGN KEY (committee_id) REFERENCES committees(id),
        FOREIGN KEY (voter_id) REFERENCES voters(id)
    )''')
    
    conn.commit()
    conn.close()

def get_db_connection():
    conn = sqlite3.connect(DB_NAME)
    conn.row_factory = sqlite3.Row
    return conn

@app.route('/')
def index():
    conn = get_db_connection()
    
    # Statistics
    total_voters = conn.execute('SELECT COUNT(*) FROM voters').fetchone()[0]
    positive = conn.execute("SELECT COUNT(*) FROM voters WHERE status = 'positive'").fetchone()[0]
    uncertain = conn.execute("SELECT COUNT(*) FROM voters WHERE status = 'uncertain'").fetchone()[0]
    negative = conn.execute("SELECT COUNT(*) FROM voters WHERE status = 'negative'").fetchone()[0]
    pending = conn.execute("SELECT COUNT(*) FROM voters WHERE status = 'pending'").fetchone()[0]
    
    # Family statistics
    family_stats = conn.execute('''
        SELECT family_name, 
               COUNT(*) as total,
               SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive,
               SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain,
               SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative
        FROM voters 
        WHERE family_name IS NOT NULL AND family_name != ''
        GROUP BY family_name
        ORDER BY total DESC
    ''').fetchall()
    
    # Center statistics
    center_stats = conn.execute('''
        SELECT electoral_center,
               COUNT(*) as total,
               SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive,
               SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain,
               SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative
        FROM voters 
        WHERE electoral_center IS NOT NULL AND electoral_center != ''
        GROUP BY electoral_center
        ORDER BY total DESC
    ''').fetchall()
    
    # Committee statistics
    committee_stats = conn.execute('''
        SELECT c.name, c.id,
               COUNT(v.id) as total,
               SUM(CASE WHEN v.status = 'positive' THEN 1 ELSE 0 END) as positive,
               SUM(CASE WHEN v.status = 'uncertain' THEN 1 ELSE 0 END) as uncertain,
               SUM(CASE WHEN v.status = 'negative' THEN 1 ELSE 0 END) as negative,
               SUM(CASE WHEN v.status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM committees c
        LEFT JOIN voters v ON v.committee_id = c.id
        GROUP BY c.id
    ''').fetchall()
    
    conn.close()
    
    return render_template('index.html', 
                           total_voters=total_voters,
                           positive=positive,
                           uncertain=uncertain,
                           negative=negative,
                           pending=pending,
                           family_stats=family_stats,
                           center_stats=center_stats,
                           committee_stats=committee_stats)

@app.route('/voters')
def voters():
    conn = get_db_connection()
    search = request.args.get('search', '')
    family_filter = request.args.get('family', '')
    center_filter = request.args.get('center', '')
    status_filter = request.args.get('status', '')
    
    query = 'SELECT v.*, c.name as committee_name FROM voters v LEFT JOIN committees c ON v.committee_id = c.id WHERE 1=1'
    params = []
    
    if search:
        query += ' AND (v.full_name LIKE ? OR v.electoral_code LIKE ?)'
        params.extend([f'%{search}%', f'%{search}%'])
    if family_filter:
        query += ' AND v.family_name = ?'
        params.append(family_filter)
    if center_filter:
        query += ' AND v.electoral_center = ?'
        params.append(center_filter)
    if status_filter:
        query += ' AND v.status = ?'
        params.append(status_filter)
    
    query += ' ORDER BY v.id DESC'
    
    voters_list = conn.execute(query, params).fetchall()
    
    # Get unique families and centers for filters
    families = conn.execute('SELECT DISTINCT family_name FROM voters WHERE family_name IS NOT NULL AND family_name != "" ORDER BY family_name').fetchall()
    centers = conn.execute('SELECT DISTINCT electoral_center FROM voters WHERE electoral_center IS NOT NULL AND electoral_center != "" ORDER BY electoral_center').fetchall()
    
    conn.close()
    
    return render_template('voters.html', 
                           voters=voters_list, 
                           families=families,
                           centers=centers,
                           search=search,
                           family_filter=family_filter,
                           center_filter=center_filter,
                           status_filter=status_filter)

@app.route('/voter/add', methods=['POST'])
def add_voter():
    full_name = request.form.get('full_name')
    family_name = request.form.get('family_name')
    electoral_code = request.form.get('electoral_code')
    electoral_center = request.form.get('electoral_center')
    
    conn = get_db_connection()
    conn.execute('INSERT INTO voters (full_name, family_name, electoral_code, electoral_center) VALUES (?, ?, ?, ?)',
                 (full_name, family_name, electoral_code, electoral_center))
    conn.commit()
    conn.close()
    
    flash('تمت إضافة الناخب بنجاح', 'success')
    return redirect(url_for('voters'))

@app.route('/voter/edit/<int:id>', methods=['POST'])
def edit_voter(id):
    full_name = request.form.get('full_name')
    family_name = request.form.get('family_name')
    electoral_code = request.form.get('electoral_code')
    electoral_center = request.form.get('electoral_center')
    status = request.form.get('status')
    notes = request.form.get('notes')
    followed_by = request.form.get('followed_by')
    
    conn = get_db_connection()
    conn.execute('''UPDATE voters SET 
                    full_name = ?, family_name = ?, electoral_code = ?, 
                    electoral_center = ?, status = ?, notes = ?,
                    followed_by = ?, followup_date = ?
                    WHERE id = ?''',
                 (full_name, family_name, electoral_code, electoral_center, 
                  status, notes, followed_by, datetime.now().strftime('%Y-%m-%d %H:%M'), id))
    conn.commit()
    conn.close()
    
    flash('تم تحديث بيانات الناخب بنجاح', 'success')
    return redirect(url_for('voters'))

@app.route('/voter/delete/<int:id>')
def delete_voter(id):
    conn = get_db_connection()
    conn.execute('DELETE FROM voters WHERE id = ?', (id,))
    conn.commit()
    conn.close()
    
    flash('تم حذف الناخب', 'success')
    return redirect(url_for('voters'))

@app.route('/import', methods=['GET', 'POST'])
def import_excel():
    if request.method == 'POST':
        if 'file' not in request.files:
            flash('الرجاء اختيار ملف', 'error')
            return redirect(request.url)
        
        file = request.files['file']
        if file.filename == '':
            flash('الرجاء اختيار ملف', 'error')
            return redirect(request.url)
        
        if file:
            try:
                df = pd.read_excel(file)
                
                # Expected columns: الاسم بالكامل, اسم العائلة, الرمز انتخابي, المركز انتخابي
                required_cols = ['الاسم بالكامل', 'اسم العائلة', 'الرمز انتخابي', 'المركز انتخابي']
                if not all(col in df.columns for col in required_cols):
                    flash(f'الأعمدة المطلوبة: {", ".join(required_cols)}', 'error')
                    return redirect(request.url)
                
                conn = get_db_connection()
                imported = 0
                
                for _, row in df.iterrows():
                    full_name = str(row['الاسم بالكامل']).strip()
                    family_name = str(row['اسم العائلة']).strip() if pd.notna(row['اسم العائلة']) else ''
                    electoral_code = str(row['الرمز انتخابي']).strip() if pd.notna(row['الرمز انتخابي']) else ''
                    electoral_center = str(row['المركز انتخابي']).strip() if pd.notna(row['المركز انتخابي']) else ''
                    
                    if full_name and full_name != 'nan':
                        conn.execute('''INSERT INTO voters (full_name, family_name, electoral_code, electoral_center) 
                                        VALUES (?, ?, ?, ?)''',
                                     (full_name, family_name, electoral_code, electoral_center))
                        imported += 1
                
                conn.commit()
                conn.close()
                
                flash(f'تم استيراد {imported} ناخب بنجاح', 'success')
                return redirect(url_for('voters'))
                
            except Exception as e:
                flash(f'خطأ في استيراد الملف: {str(e)}', 'error')
                return redirect(request.url)
    
    return render_template('import.html')

@app.route('/families')
def families():
    conn = get_db_connection()
    
    # Get all families with statistics
    families_list = conn.execute('''
        SELECT f.id, f.name,
               COUNT(v.id) as total_voters,
               SUM(CASE WHEN v.status = 'positive' THEN 1 ELSE 0 END) as positive,
               SUM(CASE WHEN v.status = 'uncertain' THEN 1 ELSE 0 END) as uncertain,
               SUM(CASE WHEN v.status = 'negative' THEN 1 ELSE 0 END) as negative,
               SUM(CASE WHEN v.status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM families f
        LEFT JOIN voters v ON v.family_name = f.name
        GROUP BY f.id
        ORDER BY total_voters DESC
    ''').fetchall()
    
    # Get families in voters but not in families table
    voter_families = conn.execute('''
        SELECT DISTINCT family_name FROM voters 
        WHERE family_name IS NOT NULL AND family_name != ''
        AND family_name NOT IN (SELECT name FROM families)
        ORDER BY family_name
    ''').fetchall()
    
    conn.close()
    
    return render_template('families.html', families=families_list, voter_families=voter_families)

@app.route('/family/add', methods=['POST'])
def add_family():
    name = request.form.get('name')
    
    conn = get_db_connection()
    try:
        conn.execute('INSERT INTO families (name) VALUES (?)', (name,))
        conn.commit()
        flash('تمت إضافة العائلة بنجاح', 'success')
    except:
        flash('العائلة موجودة مسبقاً', 'error')
    conn.close()
    
    return redirect(url_for('families'))

@app.route('/family/delete/<int:id>')
def delete_family(id):
    conn = get_db_connection()
    conn.execute('DELETE FROM families WHERE id = ?', (id,))
    conn.commit()
    conn.close()
    
    flash('تم حذف العائلة', 'success')
    return redirect(url_for('families'))

@app.route('/committees')
def committees():
    conn = get_db_connection()
    committees_list = conn.execute('''
        SELECT c.*, 
               COUNT(v.id) as assigned_voters,
               SUM(CASE WHEN v.status = 'positive' THEN 1 ELSE 0 END) as positive,
               SUM(CASE WHEN v.status = 'uncertain' THEN 1 ELSE 0 END) as uncertain,
               SUM(CASE WHEN v.status = 'negative' THEN 1 ELSE 0 END) as negative,
               SUM(CASE WHEN v.status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM committees c
        LEFT JOIN voters v ON v.committee_id = c.id
        GROUP BY c.id
    ''').fetchall()
    conn.close()
    
    return render_template('committees.html', committees=committees_list)

@app.route('/committee/add', methods=['POST'])
def add_committee():
    name = request.form.get('name')
    description = request.form.get('description')
    
    conn = get_db_connection()
    conn.execute('INSERT INTO committees (name, description) VALUES (?, ?)', (name, description))
    conn.commit()
    conn.close()
    
    flash('تمت إضافة اللجنة بنجاح', 'success')
    return redirect(url_for('committees'))

@app.route('/committee/delete/<int:id>')
def delete_committee(id):
    conn = get_db_connection()
    conn.execute('UPDATE voters SET committee_id = NULL WHERE committee_id = ?', (id,))
    conn.execute('DELETE FROM committees WHERE id = ?', (id,))
    conn.commit()
    conn.close()
    
    flash('تم حذف اللجنة', 'success')
    return redirect(url_for('committees'))

@app.route('/tasks')
def tasks():
    conn = get_db_connection()
    
    family_filter = request.args.get('family', '')
    center_filter = request.args.get('center', '')
    status_filter = request.args.get('status', '')
    
    # Get committees for assignment with voter counts
    committees = conn.execute('''
        SELECT c.*, COUNT(v.id) as voters_count 
        FROM committees c 
        LEFT JOIN voters v ON v.committee_id = c.id 
        GROUP BY c.id 
        ORDER BY c.name
    ''').fetchall()
    
    # Get voters with filters
    query = '''SELECT v.*, c.name as committee_name 
               FROM voters v 
               LEFT JOIN committees c ON v.committee_id = c.id 
               WHERE 1=1'''
    params = []
    
    if family_filter:
        query += ' AND v.family_name = ?'
        params.append(family_filter)
    if center_filter:
        query += ' AND v.electoral_center = ?'
        params.append(center_filter)
    if status_filter:
        query += ' AND v.status = ?'
        params.append(status_filter)
    
    query += ' ORDER BY v.family_name, v.electoral_center'
    
    voters_list = conn.execute(query, params).fetchall()
    
    # Get unique families with counts
    families = conn.execute('''
        SELECT family_name as family_name, COUNT(*) as count 
        FROM voters 
        WHERE family_name IS NOT NULL AND family_name != "" 
        GROUP BY family_name 
        ORDER BY family_name
    ''').fetchall()
    
    # Get unique centers with counts
    centers = conn.execute('''
        SELECT electoral_center as electoral_center, COUNT(*) as count 
        FROM voters 
        WHERE electoral_center IS NOT NULL AND electoral_center != "" 
        GROUP BY electoral_center 
        ORDER BY electoral_center
    ''').fetchall()
    
    conn.close()
    
    return render_template('tasks.html', 
                           committees=committees, 
                           voters=voters_list,
                           families=families,
                           centers=centers,
                           family_filter=family_filter,
                           center_filter=center_filter,
                           status_filter=status_filter)

@app.route('/assign_task', methods=['POST'])
def assign_task():
    voter_ids = request.form.getlist('voter_ids')
    committee_id = request.form.get('committee_id')
    
    if not voter_ids or not committee_id:
        flash('الرجاء اختيار ناخبين ولجنة', 'error')
        return redirect(url_for('tasks'))
    
    conn = get_db_connection()
    for voter_id in voter_ids:
        conn.execute('UPDATE voters SET committee_id = ? WHERE id = ?', (committee_id, voter_id))
    conn.commit()
    conn.close()
    
    flash(f'تم توزيع {len(voter_ids)} ناخب على اللجنة بنجاح', 'success')
    return redirect(url_for('tasks'))

@app.route('/reports')
def reports():
    conn = get_db_connection()
    
    # Overall statistics
    stats = conn.execute('''
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive,
            SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain,
            SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM voters
    ''').fetchone()
    
    # Family breakdown
    family_report = conn.execute('''
        SELECT family_name,
               COUNT(*) as total,
               SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive,
               SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain,
               SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative,
               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM voters
        WHERE family_name IS NOT NULL AND family_name != ''
        GROUP BY family_name
        ORDER BY positive DESC
    ''').fetchall()
    
    # Center breakdown
    center_report = conn.execute('''
        SELECT electoral_center,
               COUNT(*) as total,
               SUM(CASE WHEN status = 'positive' THEN 1 ELSE 0 END) as positive,
               SUM(CASE WHEN status = 'uncertain' THEN 1 ELSE 0 END) as uncertain,
               SUM(CASE WHEN status = 'negative' THEN 1 ELSE 0 END) as negative,
               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM voters
        WHERE electoral_center IS NOT NULL AND electoral_center != ''
        GROUP BY electoral_center
        ORDER BY positive DESC
    ''').fetchall()
    
    # Committee breakdown
    committee_report = conn.execute('''
        SELECT c.name as committee_name,
               COUNT(v.id) as total,
               SUM(CASE WHEN v.status = 'positive' THEN 1 ELSE 0 END) as positive,
               SUM(CASE WHEN v.status = 'uncertain' THEN 1 ELSE 0 END) as uncertain,
               SUM(CASE WHEN v.status = 'negative' THEN 1 ELSE 0 END) as negative,
               SUM(CASE WHEN v.status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM committees c
        LEFT JOIN voters v ON v.committee_id = c.id
        GROUP BY c.id
        ORDER BY positive DESC
    ''').fetchall()
    
    conn.close()
    
    return render_template('reports.html',
                           stats=stats,
                           family_report=family_report,
                           center_report=center_report,
                           committee_report=committee_report)

@app.route('/api/stats')
def api_stats():
    conn = get_db_connection()
    
    stats = {
        'total': conn.execute('SELECT COUNT(*) FROM voters').fetchone()[0],
        'positive': conn.execute("SELECT COUNT(*) FROM voters WHERE status = 'positive'").fetchone()[0],
        'uncertain': conn.execute("SELECT COUNT(*) FROM voters WHERE status = 'uncertain'").fetchone()[0],
        'negative': conn.execute("SELECT COUNT(*) FROM voters WHERE status = 'negative'").fetchone()[0],
        'pending': conn.execute("SELECT COUNT(*) FROM voters WHERE status = 'pending'").fetchone()[0]
    }
    
    conn.close()
    return jsonify(stats)

@app.route('/api/family/<family_name>')
def api_family(family_name):
    conn = get_db_connection()
    
    voters = conn.execute('''
        SELECT v.*, c.name as committee_name 
        FROM voters v 
        LEFT JOIN committees c ON v.committee_id = c.id
        WHERE v.family_name = ?
        ORDER BY v.electoral_center
    ''', (family_name,)).fetchall()
    
    result = []
    for v in voters:
        result.append({
            'id': v['id'],
            'full_name': v['full_name'],
            'electoral_code': v['electoral_code'],
            'electoral_center': v['electoral_center'],
            'status': v['status'],
            'committee_name': v['committee_name']
        })
    
    conn.close()
    return jsonify(result)

@app.route('/template')
def download_template():
    # Create a sample Excel template
    data = {
        'الاسم بالكامل': ['أحمد محمدكريم', 'خالد إبراهيم Hassan'],
        'اسم العائلة': ['كريم', 'حسن'],
        'الرمز انتخابي': ['12345', '12346'],
        'المركز انتخابي': ['مدرسة الشهيد عبد القادر', 'مدرسة الحرية']
    }
    df = pd.DataFrame(data)
    
    output = BytesIO()
    with pd.ExcelWriter(output, engine='openpyxl') as writer:
        df.to_excel(writer, sheet_name='الناخبين', index=False)
    
    output.seek(0)
    return send_file(
        output,
        mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        as_attachment=True,
        download_name='template.xlsx'
    )

if __name__ == '__main__':
    init_db()
    app.run(host='0.0.0.0', port=5000, debug=True)
