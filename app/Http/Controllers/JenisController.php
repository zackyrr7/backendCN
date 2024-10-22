<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class JenisController extends Controller
{
    public function index($id)
    {
        $jenis = DB::select("SELECT a.jenis_id,a.nama,a.tipe,a.user_id,b.icon,c.warna FROM jenis as a
                JOIN icons as b on a.icon = b.id
                JOIN warnas as c on a.warna = c.warna_id
                WHERE a.user_id = ?", [$id]);
        return response()->json($jenis);
    }

    public function tambah(Request $request)
    {
        try {
            $total = DB::table('jenis')->where('nama', $request->nama)->count();
            if ($total > 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Nama Sudah Terdaftar'
                ]);
            }

            $jenis = DB::table('jenis')->insert([
                'jenis_id' => Str::random(8),
                'nama' => $request->nama,
                'user_id' => $request->user_id,
                'tipe' => $request->tipe,
                'warna' => $request->warna,
                'icon' => $request->icon,
            ]);
            DB::commit();
            return response()->json([
                'message' => "data Berhasil di simpan",
                'jenis' => $jenis
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => "data gagal di simpan",
                'error' => $th
            ]);
        }
    }
    public function edit(Request $request)
    {
        try {
            $jenis = DB::table('jenis')->where('user_id', $request->user_id,)->update([
                // 'jenis_id' => Str::random(8),
                'nama' => $request->nama,
                'tipe' => $request->tipe,
                'warna' => $request->warna,
                'icon' => $request->icon,
            ]);
            DB::commit();
            return response()->json([
                'message' => "data Berhasil di simpan",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => "data gagal di simpan",
                'error' => $th
            ]);
        }
    }

    public function hapus($user_id, $jenis_id)
    {
        try {
            DB::table('jenis')->where(['user_id' => $user_id, 'jenis_id' => $jenis_id])
                ->delete();
            DB::commit();
            return response()->json([
                'message' => "data Berhasil di hapus",
            ]);
        } catch (\Throwable $th) {
            DB::commit();
            return response()->json([
                'message' => "data gagal di hapus",
                'error' => $th
            ]);
        }
    }


    public function iconTambah(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'icon' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validasi file gambar

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction(); // Memulai transaksi

            // Menyimpan file gambar
            if ($request->hasFile('icon')) {
                $file = $request->file('icon');
                $path = $file->store('icons', 'public'); // Menyimpan file ke direktori 'icons' di storage/app/public

                // Menyimpan path file ke dalam basis data
                $iconId = DB::table('icons')->insertGetId([
                    'icon' => $path,
                    'id' =>  Str::random(8),

                ]);

                DB::commit(); // Menyimpan transaksi
                return response()->json([
                    'message' => 'Data berhasil disimpan',
                    'icon_id' => $iconId,
                    'icon_path' => $path,
                ]);
            }

            return response()->json([
                'message' => 'File gambar tidak ditemukan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack(); // Membatalkan transaksi jika terjadi kesalahan
            return response()->json([
                'message' => 'Data gagal disimpan',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function hapusIcon(Request $request, $id)
    {
        try {
            DB::beginTransaction(); // Mulai transaksi

            // Ambil data ikon berdasarkan ID
            $icon = DB::table('icons')->where('id', $id)->first();

            if (!$icon) {
                // Jika ikon tidak ditemukan
                return response()->json([
                    'message' => 'Ikon tidak ditemukan',
                ], 404);
            }

            // Hapus file gambar dari filesystem
            if (Storage::disk('public')->exists($icon->icon)) {
                Storage::disk('public')->delete($icon->icon); // Hapus file gambar
            }

            // Hapus data dari database
            DB::table('icons')->where('id', $id)->delete();

            DB::commit(); // Simpan transaksi

            return response()->json([
                'message' => 'Ikon berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack(); // Batalkan transaksi jika terjadi kesalahan
            return response()->json([
                'message' => 'Ikon gagal dihapus',
                'error' => $th->getMessage(), // Tampilkan pesan kesalahan
            ], 500);
        }
    }

    public function tambahWarna(Request $request)
    {
        try {


            $jenis = DB::table('warnas')->insert([
                'warna_id' => Str::random(8),
                'warna' => $request->warna,

            ]);
            DB::commit();
            return response()->json([
                'message' => "data Berhasil di simpan",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => "data gagal di simpan",
                'error' => $th
            ]);
        }
    }

    public function hapusWarna($warna_id)
    {
        try {
            DB::table('warnas')->where(['warna_id' => $warna_id])
                ->delete();
            DB::commit();
            return response()->json([
                'message' => "data Berhasil di hapus",
            ]);
        } catch (\Throwable $th) {
            DB::commit();
            return response()->json([
                'message' => "data gagal di hapus",
                'error' => $th
            ]);
        }
    }
}
