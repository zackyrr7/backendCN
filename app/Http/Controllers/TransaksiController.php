<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class TransaksiController extends Controller
{
    public function indexUser($user_id)
    {
        $bulanSekarang = Carbon::now()->format('m');
        $data = DB::select("SELECT a.nama,a.total,a.tanggal, c.icon,d.warna,b.tipe FROM transaksi as a JOIN
                            jenis as b on a.jenis_id = b.jenis_id JOIN
                            icons as c ON b.icon = c.id JOIN
                            warnas as d ON b.warna = d.warna_id
                            WHERE a.user_id = ? and MONTH(a.tanggal) = ?
                            ORDER BY a.tanggal", [$user_id, $bulanSekarang]);
        return response()->json($data);
    }

    public function tambah(Request $request)
    {
        try {
            DB::table('transaksi')->insert([
                'transaksi_id' => Str::random(8),
                'nama' => $request->nama,
                'jenis_id' => $request->jenis_id,
                'total' => $request->total,
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
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
    public function tambahgoal(Request $request)
    {
        try {
            DB::table('transaksi')->insert([
                'transaksi_id' => Str::random(8),
                'nama' => $request->nama,
                'goal_id' => $request->goal_id,
                'total' => $request->total,
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
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
    public function tambahhutang(Request $request)
    {
        try {
            DB::table('transaksi')->insert([
                'transaksi_id' => Str::random(8),
                'nama' => $request->nama,
                'hutang_id' => $request->hutang_id,
                'total' => $request->total,
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
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
    public function edit(Request $request)
    {
        try {
            DB::table('transaksi')->where('transaksi_id', $request->transaksi)->update([
                'nama' => $request->nama,
                'jenis_id' => $request->jenis_id,
                'total' => $request->total,
                'tanggal' => $request->tanggal,
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
    public function editgoal(Request $request)
    {
        try {
            DB::table('transaksi')->where('transaksi_id', $request->transaksi)->update([
                'nama' => $request->nama,
                'total' => $request->total,
                'tanggal' => $request->tanggal,
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

    public function hapus($transaksi_id)
    {
        try {
            DB::table('transaksi')->where(['transaksi_id' => $transaksi_id])
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
