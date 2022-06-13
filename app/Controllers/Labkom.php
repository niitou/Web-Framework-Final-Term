<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Labkom as ModelsLabkom;
use App\Models\Member;
use App\Models\ReservasiLabkom;

class Labkom extends BaseController
{

    public function __construct()
    {
        $this->labkomModel = new ModelsLabkom();
        $this->memberModel = new Member();
        $this->reservasi = new ReservasiLabkom();
    }
    public function index()
    {
        $data = [
            'path' => 'labkom',
            'rpl' => $this->labkomModel->find(1),
            'mulmed' => $this->labkomModel->find(2),
            'tkj' => $this->labkomModel->find(3)
        ];
        return view('lab_vw', $data);
    }

    public function update_modal($id)
    {
        if ($this->request->isAJAX()) {
            $data = [
                'item' => $this->labkomModel->find($id)
            ];

            $result = [
                'data' => view('/template/facility.php', $data)
            ];

            return $this->response->setJSON($result);
        } else {
            exit('data tidak dapat ditampilkan');
        }
    }

    public function update($id)
    {
        $input = [
            'id' => $id,
            'pc' => $this->request->getVar('labkom-pc'),
            'meja' => $this->request->getVar('labkom-meja'),
            'kursi' => $this->request->getVar('labkom-kursi'),
            'papan tulis' => $this->request->getVar('labkom-papan'),
            'papan tulis' => $this->request->getVar('labkom-papan'),
            'penghapus' => $this->request->getVar('labkom-penghapus'),
            'penghapus' => $this->request->getVar('labkom-penghapus'),
            'kabel VGA' => $this->request->getVar('labkom-vga')
        ];

        $this->labkomModel->save($input);
        $pesan = [
            'sukses' => "Data telah diupdate"
        ];
        return $this->response->setJSON($pesan);
    }

    public function reserve()
    {
        $userData = $this->memberModel->find(session()->get('id'));
        $waktu_pinjam = $this->request->getVar('peminjaman') ." " .$this->request->getVar('jam'); 
        $waktu_pinjam = date('Y-m-d H:i:s', strtotime($waktu_pinjam));

        $waktu_selesai = date('Y-m-d H:i:s', strtotime($waktu_pinjam . sprintf(' + %u hour', $this->request->getVar('duration'))) );
        $input = [
            'peminjam' => $userData[0]['nama'],
            'labkom' => $this->request->getVar('labkom-opt'),
            'waktu_peminjaman' => date('Y-m-d h:i:s', time()),
            'waktu_penggunaan' => $waktu_pinjam,
            'waktu_akhir_penggunaan' => $waktu_selesai,
            'status' => 'unfinished',
            'catatan' => $this->request->getVar('reser-notes')
        ];
        print_r($input);
        $db = db_connect();
        $query = $db->query('SELECT * FROM reservasi_labkom WHERE `peminjam` = \'' .$userData[0]['nama'].'\'AND'.'`labkom` = \''. $this->request->getVar('labkom-opt').'\' AND waktu_penggunaan >= \'' . $waktu_pinjam . '\' AND waktu_akhir_penggunaan <= \'' . $waktu_selesai . '\'');
        print_r($query->getResult());
        if (count($query->getResult()) == 0) {
            $this->reservasi->save($input);
            $pesan = [
                'sukses' => 'Berhasil Reservasi'
            ];
        }else{
            $pesan = [
                'gagal' => 'Waktu anda bertabrakan dengan jadwal lain'
            ];
        }
        return $this->response->setJSON($pesan);
    }
}
