<?php
$root='C:/Users/Tenma/enrolify';
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$expected='C:/Users/Tenma/AppData/Local/hermes/profiles/redmess/cache/scratch/current-browser/acceptance.sqlite';
if(config('database.connections.sqlite.database')!==$expected)throw new RuntimeException('Not isolated DB');
config(['filesystems.disks.local.root'=>dirname($expected).'/private']);
@mkdir(dirname($expected).'/sessions',0777,true);
// Purpose-built examiner data, NOT evidence of student/review/schedule UI success.
$supervisor=App\Models\User::where('email','supervisor@acceptance.invalid')->firstOrFail();
$second=App\Models\User::where('email','examiner@acceptance.invalid')->firstOrFail();
$title=App\Models\PengajuanJudul::create(['mahasiswa_id'=>1,'judul_ta'=>'Fixture examiner thesis','bidang_penelitian'=>'Web','pembimbing_1'=>$supervisor->id,'status'=>'disetujui']);
$period=App\Models\SchedulePeriod::create(['name'=>'Acceptance period','tipe'=>'sempro','gelombang'=>1,'capacity'=>10,'start_date'=>'2026-11-01','end_date'=>'2026-11-02','slots'=>[['tanggal'=>'2026-11-01','jam_mulai'=>'09:00:00','jam_selesai'=>'10:00:00']],'is_open'=>true]);
$doc='dokumen/sempro/1/acceptance.pdf';
// Minimal valid one-page PDF built locally with offsets.
$objects=['<< /Type /Catalog /Pages 2 0 R >>','<< /Type /Pages /Kids [3 0 R] /Count 1 >>','<< /Type /Page /Parent 2 0 R /MediaBox [0 0 300 200] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>','<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
$stream='BT /F1 12 Tf 20 100 Td (Isolated acceptance document) Tj ET';
$objects[]='<< /Length '.strlen($stream).' >>'."\nstream\n".$stream."\nendstream";
$pdf="%PDF-1.4\n";$offsets=[0];foreach($objects as $i=>$o){$offsets[]=strlen($pdf);$pdf.=($i+1)." 0 obj\n$o\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 6\n0000000000 65535 f \n";foreach(array_slice($offsets,1)as$o)$pdf.=sprintf('%010d 00000 n ',$o)."\n";$pdf.="trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n";
Illuminate\Support\Facades\Storage::disk('local')->put($doc,$pdf);
$row=App\Models\PendaftaranSempro::create(['mahasiswa_id'=>1,'judul_ta'=>'Fixture examiner thesis','bentuk_ta'=>'Skripsi','gelombang'=>1,'status'=>'terjadwal','status_akademik'=>'disetujui','proposal_file'=>$doc]);
$exam=App\Models\Penjadwalan::create(['mahasiswa_id'=>1,'pendaftaran_id'=>$row->id,'period_id'=>$period->id,'tipe'=>'sempro','tanggal'=>'2026-11-01','jam_mulai'=>'09:00:00','jam_selesai'=>'10:00:00','ruangan_id'=>1,'pembimbing_1_id'=>$supervisor->id,'penguji_1_id'=>2,'penguji_2_id'=>$second->id,'status'=>'terpublikasi']);
echo json_encode(['database'=>$expected,'period'=>$period->id,'exam'=>$exam->id,'document_sha256'=>hash('sha256',$pdf),'source'=>'direct scratch fixture, not UI proof'],JSON_PRETTY_PRINT);
