import pdfplumber
import sqlite3
import glob
import re
from datetime import datetime
import os

DB_PATH = 'database/database.sqlite'

def parse_date(date_str, time_str):
    # date_str: 09/03/2026
    # time_str: 09:50
    return datetime.strptime(f"{date_str} {time_str}", "%d/%m/%Y %H:%M").strftime("%Y-%m-%d %H:%M:00")

def extract_pdfs():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    # Optional: Clear existing data for schedules and users (dosen)
    cursor.execute("DELETE FROM schedules")
    
    pdf_files = glob.glob('*.pdf')
    for pdf_path in pdf_files:
        print(f"Parsing {pdf_path}...")
        try:
            with pdfplumber.open(pdf_path) as pdf:
                for page in pdf.pages:
                    tables = page.extract_tables()
                    for table in tables:
                        for row in table:
                            if not row or not row[0] or row[0] == 'No' or 'No\n' in row[0]: continue
                            if len(row) < 10: continue
                            
                            # Clean newlines from strings
                            clean_row = [str(cell).replace('\n', ' ') if cell else '' for cell in row]
                            
                            # Skip merged header rows or invalid rows
                            if clean_row[0].startswith('Dicetak'): continue
                            
                            periode = clean_row[2] # 2025 Genap
                            prodi_name = clean_row[3]
                            
                            # Clean up garbage strings from prodi_name (like timestamp and URLs from PDF footer)
                            import re
                            prodi_name = re.sub(r' \d+ WIB \|.*$', '', prodi_name)
                            prodi_name = re.sub(r' \d+:\d+ WIB \|.*$', '', prodi_name)
                            prodi_name = re.sub(r' WIB \|.*$', '', prodi_name)
                            prodi_name = prodi_name.strip()
                            if "D1 - Modul Digitalisasi Administrasi Perkantoran" in prodi_name:
                                prodi_name = "D1 - Modul"
                            
                            mata_kuliah = clean_row[4]
                            kelas = clean_row[5]
                            pertemuan = clean_row[6]
                            jadwal_raw = clean_row[7]
                            status = clean_row[8]
                            dosen_raw = clean_row[9]
                            
                            if not jadwal_raw or len(jadwal_raw.split(' ')) < 2: continue
                            
                            # Parse jadwal
                            # Example: Senin, 09/03/2026 (09:50 - 12:00)
                            match_jadwal = re.search(r'(\d{2}/\d{2}/\d{4})\s*\(([\d:]+)\s*-\s*([\d:]+)\)', jadwal_raw)
                            if not match_jadwal: continue
                            
                            date_str, start_time, end_time = match_jadwal.groups()
                            waktu_mulai = parse_date(date_str, start_time)
                            waktu_selesai = parse_date(date_str, end_time)
                            
                            # Parse Dosen
                            # Example: 9904016685 - JOHANNES TRIANGKA SETIADJI
                            if ' - ' in dosen_raw:
                                nidn, dosen_name = dosen_raw.split(' - ', 1)
                            else:
                                dosen_name = dosen_raw
                                nidn = None
                                
                            # 1. Insert Prodi if not exists
                            cursor.execute("SELECT id FROM prodis WHERE name = ?", (prodi_name,))
                            prodi_row = cursor.fetchone()
                            if prodi_row:
                                prodi_id = prodi_row[0]
                            else:
                                cursor.execute("INSERT INTO prodis (name, created_at, updated_at) VALUES (?, datetime('now'), datetime('now'))", (prodi_name,))
                                prodi_id = cursor.lastrowid
                                
                            # 2. Insert Dosen if not exists
                            dosen_email = f"dosen_{nidn or dosen_name.replace(' ', '').lower()}@lpkia.ac.id"
                            cursor.execute("SELECT id FROM users WHERE name = ?", (dosen_name,))
                            dosen_row = cursor.fetchone()
                            if dosen_row:
                                dosen_id = dosen_row[0]
                            else:
                                cursor.execute("""
                                    INSERT INTO users (name, email, password, role, prodi_id, created_at, updated_at) 
                                    VALUES (?, ?, ?, 'dosen', ?, datetime('now'), datetime('now'))
                                """, (dosen_name, dosen_email, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', prodi_id)) # Password is 'password'
                                dosen_id = cursor.lastrowid
                                
                            # 3. Insert Schedule
                            cursor.execute("""
                                INSERT INTO schedules (user_id, prodi_id, periode, mata_kuliah, kelas, pertemuan, waktu_mulai, waktu_selesai, status, created_at, updated_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
                            """, (dosen_id, prodi_id, periode, mata_kuliah, kelas, pertemuan, waktu_mulai, waktu_selesai, status))
        except Exception as e:
            print(f"Error parsing {pdf_path}: {e}")
            
    conn.commit()
    conn.close()
    print("Database seeded from PDFs successfully!")

if __name__ == '__main__':
    extract_pdfs()
