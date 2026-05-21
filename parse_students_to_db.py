import pdfplumber
import sqlite3
import glob
import re
import os

DB_PATH = 'database/database.sqlite'

def map_prodi_from_filename(filename):
    fn = filename.lower()
    if "teknik informatika" in fn:
        return "S1 - Teknik Informatika"
    elif "sistem informasi" in fn:
        return "S1 - Sistem Informasi"
    elif "s1 akuntansi" in fn or "s1_akuntansi" in fn:
        return "S1 - Akuntansi"
    elif "komputerisasi akuntansi" in fn or "d3 akuntansi" in fn:
        return "D3 - Akuntansi"
    elif "s1 administrasi bisnis" in fn:
        return "S1 - Administrasi Bisnis"
    elif "d3 administrasi bisnis" in fn:
        return "D3 - Administrasi Bisnis"
    return "Umum"

def parse_students():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    # Empty existing students to prevent stale data
    print("Clearing mahasiswas table...")
    cursor.execute("DELETE FROM mahasiswas")
    
    pdf_files = glob.glob('Rekap Presensi Mahasiswa *.pdf')
    total_students_inserted = 0
    total_students_updated = 0
    
    for pdf_path in pdf_files:
        filename = os.path.basename(pdf_path)
        prodi_name = map_prodi_from_filename(filename)
        
        # Get or insert prodi
        cursor.execute("SELECT id FROM prodis WHERE name = ?", (prodi_name,))
        prodi_row = cursor.fetchone()
        if prodi_row:
            prodi_id = prodi_row[0]
        else:
            cursor.execute("INSERT INTO prodis (name, created_at, updated_at) VALUES (?, datetime('now'), datetime('now'))", (prodi_name,))
            prodi_id = cursor.lastrowid
            
        print(f"Parsing student PDF: {filename} -> Prodi: {prodi_name} (ID: {prodi_id})")
        
        try:
            with pdfplumber.open(pdf_path) as pdf:
                for page_num, page in enumerate(pdf.pages):
                    text = page.extract_text()
                    if not text:
                        continue
                        
                    # Extract Kelas
                    match_kelas = re.search(r'Nama Kelas\s*:\s*([^\s\n]+)', text)
                    kelas_name = match_kelas.group(1).strip() if match_kelas else "Umum"
                    
                    # Extract Mata Kuliah (just in case)
                    match_mk = re.search(r'Matakuliah/Blok\s*:\s*([^\n]+)', text)
                    mk_name = match_mk.group(1).strip() if match_mk else ""
                    
                    # Determine status_mengulang
                    # If "mengulang" is in the class name or mata kuliah name
                    is_mengulang = False
                    if "mengulang" in kelas_name.lower() or "mengulang" in mk_name.lower():
                        is_mengulang = True
                    elif "2akkp" in kelas_name.lower(): # special check for Komputerisasi Akuntansi classes if they repeat
                        pass
                        
                    tables = page.extract_tables()
                    for table in tables:
                        for row in table:
                            if not row or not row[0]:
                                continue
                            if row[0] == 'No.' or 'No' in row[0] or row[0].startswith('Dicetak'):
                                continue
                            if len(row) < 4:
                                continue
                                
                            nim = str(row[1]).strip()
                            nama = str(row[2]).strip()
                            
                            # Clean NIM
                            nim = re.sub(r'\s+', '', nim)
                            if not nim or not nim.isdigit():
                                continue
                                
                            email = f"mhs_{nim}@student.lpkia.ac.id"
                            
                            # Insert/Update Mahasiswa
                            cursor.execute("SELECT id, status_mengulang FROM mahasiswas WHERE nim = ?", (nim,))
                            existing = cursor.fetchone()
                            
                            if existing:
                                # Update class and mengulang status if we found a repeating class
                                new_mengulang = existing[1] or is_mengulang
                                cursor.execute("""
                                    UPDATE mahasiswas 
                                    SET kelas = ?, status_mengulang = ?, updated_at = datetime('now')
                                    WHERE id = ?
                                """, (kelas_name, 1 if new_mengulang else 0, existing[0]))
                                total_students_updated += 1
                            else:
                                cursor.execute("""
                                    INSERT INTO mahasiswas (nim, nama, email, prodi_id, kelas, status_mengulang, created_at, updated_at)
                                    VALUES (?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
                                """, (nim, nama, email, prodi_id, kelas_name, 1 if is_mengulang else 0))
                                total_students_inserted += 1
                                
        except Exception as e:
            print(f"Error parsing PDF {filename}: {e}")
            
    conn.commit()
    conn.close()
    print(f"Students parsing completed! Inserted: {total_students_inserted}, Updated: {total_students_updated}")

if __name__ == '__main__':
    parse_students()
