#!/usr/bin/env python3
"""
Create sample Excel file for testing the election system
"""

import openpyxl
from openpyxl import Workbook

wb = Workbook()
ws = wb.active
ws.title = "الناخبين"

# Header row
headers = ["الاسم الكامل", "اسم العائلة", "الرمز electionي", "المركز electionي"]
ws.append(headers)

# Sample data - Family:كريم (Kareem)
ws.append(["كريم أحمد محمد", "كريم", "EL2026001", "المركز electionي الشهيد عبد القادر"])
ws.append(["علي كريم", "كريم", "EL2026002", "المركز electionي الشهيد عبد القادر"])
ws.append(["سارة كريم", "كريم", "EL2026003", "المركز electionي الشهيد عبد القادر"])

# Family:علام (Allam)
ws.append(["محمد علام", "علام", "EL2026041", "مدرسة علي佔"])
ws.append(["فاطمة علام", "علام", "EL2026042", "مدرسة علي佔"])

# Family:بوبكره (Boukhadra)
ws.append(["ياسين بوبكره", "بوبكره", "EL2026071", "المركز electionي الشهيد عبد القادر"])
ws.append(["أمينة بوبكره", "بوبكره", "EL2026072", "المركز electionي الشهيد عبد القادر"])

# Family:لعرابة (Laaraba)
ws.append(["رشيد لعرابة", "لعرابة", "EL2026091", "مركز الرشيد"])
ws.append(["خالد لعرابة", "لعرابة", "EL2026092", "مركز الرشيد"])

# Save file
wb.save("/workspace/project/ent5abat2026/نموذج_ناخبين.xlsx")
print("Created: /workspace/project/ent5abat2026/نموذج_ناخبين.xlsx")