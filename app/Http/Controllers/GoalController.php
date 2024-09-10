<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;
use Illuminate\Support\Str;

class GoalController extends Controller
{


    public function indexUser($user_id)
    {
        $data = DB::select("SELECT a.*, (SELECT SUM(b.total) FROM transaksi as b 
        WHERE a.goal_id = b.goal_id) as total  
        FROM goal as a where a.user_id = ?", [$user_id]);
        return response()->json($data);
    }
    public function tambah(Request $request)
    {

        try {
            DB::table('goal')->insert([
                'goal_id' => Str::random(4),
                'nama' => $request->nama,
                'user_id' => $request->user_id,
                'target' => $request->target,
                'status' => $request->status,
                'warna' => $request->warna,
                'icon' => $request->icon
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


    public function bagi(Request $request)
    {
        $goal_id = $request->goal_id;
        $data = DB::table('goal')->where('goal_id', $goal_id)->first();

        try {
            DB::table('goal')->insert([
                'goal_id' => $data->goal_id,
                'nama' => $data->nama,
                'user_id' => $request->user_id,
                'target' => $data->target,
                'status' => 1,
                'warna' => $data->warna,
                'icon' => $data->icon
            ]);
            DB::table('goal')->where('goal_id', $goal_id)->update([
                'status' => 1,
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


    public function update(Request $request)
    {
        try {
            DB::table('goal')->where('goal_id')->update([
                'nama' => $request->nama,
                'target' => $request->target,
                'warna' => $request->warna,
                'icon' => $request->icon
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

    public function hapus($goal_id)
    {
        try {
            DB::table('goal')->where(['goal_id' => $goal_id])
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
