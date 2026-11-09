import subprocess
import os
import tempfile

edge_path = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
html_path = os.path.abspath("contrat_template.html")
pdf_path = os.path.abspath("CONTRAT_PRESTATION_SITE_WEB_FEE_BTP.pdf")
temp_dir = os.path.join(tempfile.gettempdir(), "edge_pdf_profile")
os.makedirs(temp_dir, exist_ok=True)

url = "file:///" + html_path.replace("\\", "/")

cmd = [
    edge_path,
    "--headless=new",
    "--disable-gpu",
    "--no-sandbox",
    "--disable-extensions",
    f"--user-data-dir={temp_dir}",
    "--no-pdf-header-footer",
    f"--print-to-pdf={pdf_path}",
    url
]

print(f"Running: {' '.join(cmd)}")
res = subprocess.run(cmd, capture_output=True, text=True, timeout=20)
print("Return code:", res.returncode)

if os.path.exists(pdf_path):
    print(f"SUCCESS: PDF created ({os.path.getsize(pdf_path)} bytes)")
else:
    print("FAILED: PDF not created")
