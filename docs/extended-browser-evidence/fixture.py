import sqlite3,json, pathlib
scratch=pathlib.Path('C:/Users/Tenma/AppData/Local/hermes/profiles/redmess/cache/scratch/extended-browser')
db=scratch/'acceptance.sqlite'
assert db.exists() and db.name=='acceptance.sqlite' and db.parent.name=='extended-browser'
c=sqlite3.connect(db)
c.execute("insert into users(name,email,password,role,username) select 'Fixture Supervisor','supervisor@extended.invalid',password,'dosen','fixture-supervisor' from users where id=2")
c.execute("insert into users(name,email,password,role,username) select 'Fixture Second Examiner','examiner@extended.invalid',password,'dosen','fixture-examiner' from users where id=2")
c.execute("insert into pengajuan_judul(mahasiswa_id,judul_ta,bidang_penelitian,pembimbing_1_id,status) values(1,'Extended isolated thesis','Web',5,'menunggu')")
for tipe in ['sempro','sidang']:
 c.execute("insert into schedule_periods(name,tipe,gelombang,capacity,start_date,end_date,slots,is_open) values(?,?,1,10,'2026-11-01','2026-11-02',?,1)",('Extended '+tipe,tipe,json.dumps([dict(tanggal='2026-11-01',jam_mulai='09:00:00',jam_selesai='10:00:00'),dict(tanggal='2026-11-02',jam_mulai='10:00:00',jam_selesai='11:00:00')])) )
c.commit()
# A minimal local PDF is an upload fixture, not an official document.
pdf=b'%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n'
(scratch/'upload.pdf').write_bytes(pdf)
print(json.dumps(dict(source='direct fixture setup, NOT UI proof',title=1,supervisor=5,examiners=[2,6],periods=[1,2])))
