import pdfplumber
import sys

def extract(pdf_path):
    print(f"Extracting: {pdf_path}")
    with pdfplumber.open(pdf_path) as pdf:
        # Just extract first 2 pages
        for i, page in enumerate(pdf.pages[:2]):
            print(f"--- PAGE {i+1} ---")
            text = page.extract_text()
            print(text)
            print("--- TABLES ---")
            tables = page.extract_tables()
            for table in tables:
                for row in table:
                    print(row)
            print("\n")

if __name__ == "__main__":
    extract(sys.argv[1])
