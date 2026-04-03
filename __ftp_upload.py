from pathlib import Path 
from ftplib import FTP, error_perm 
import os 
root=Path(r'C:\Users\shiro\OneDrive\Desktop\HMS') 
host='ftpupload.net' 
user='if0_41453030' 
pwd='u8W8gwlvMynyC' 
ftp=FTP(host,timeout=40) 
ftp.login(user,pwd) 
print('logged in') 
try: 
    ftp.cwd('htdocs') 
except Exception: 
    pass 
def ensure_dir(path): 
    parts=[p for p in path.replace('\\','/').split('/') if p] 
    cur='' 
    for p in parts: 
        cur = cur + '/' + p 
        try: 
            ftp.mkd(cur) 
        except Exception: 
            pass 
skip={'.git','node_modules','vendor','__pycache__'} 
for p in root.rglob('*'): 
    rel=p.relative_to(root).as_posix() 
    if any(part in skip for part in p.parts): 
        continue 
    if p.is_dir(): 
        ensure_dir(rel) 
        continue 
    ensure_dir(str(Path(rel).parent).replace('\\','/')) 
    with open(p,'rb') as f: 
        ftp.storbinary(f'STOR {rel}',f) 
    uploaded +=  
    if uploaded %% 15 == 0: 
        print('uploaded',uploaded) 
print('done',uploaded) 
ftp.quit() 
