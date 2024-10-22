<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Helper\Helper;

class UserController extends Controller
{
    public function index()
    {
        $user = DB::table('user')->get();

        return response()->json($user);
    }

    public function store(Request $request)
    {
        $name = $request->name;
        $password = $request->password;
        $username = $request->username;

        try {

            $user = DB::table('user')->insertGetId([
                'name' => $name,
                'username' => $username,
                'password' => Hash::make($password),
                'user_id' => Str::random(8),
                'status' => 0,
            ]);
            DB::commit();
            return response()->json([
                'message' => "data Berhasil di simpan",
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => "Gagal menambahkan",
                'error' => $th
            ]);
        }
    }
    public function edit(Request $request, $id)
    {

        try {
            $update = DB::table('user')->where('user_id', $id)->update(
                [
                    'name' => $request->name,
                    'username' => $request->username,
                ]
            );
            DB::commit();
            return response()->json([
                'message' => 'Nama Berhasil di update'
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'error',
                    'error' => $th
                ]
            );
        }
    }

    public function login(Request $request)
    {
        // $username = $request->username;
        // $password = $request->password;

        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = DB::table('user')->where(['username' => $request->username])->first();
        }
        if ($user != null) {
            Auth::logoutOtherDevices($request->password);
            $request->session()->regenerate();
            $sesi = session()->getID();
            $sesi2 = DB::table('user')->where(['username' => $request->username])->update([
                'session_id' => $sesi
            ]);
            DB::commit();
            $currentDate = Carbon::now();
            $bulan = $currentDate->month;
            $bulanNama = bulanNama($bulan);
            $data = [
                'session_id' => $sesi,
                'bulan' => $bulanNama,
                'succes' => true,
                'user_id' => $user->user_id
            ];

            return response()->json($data);
        } else {
            return response()->json(
                ['message' => 'Username atau password anda salah']
            );
        }
    }

    public function dashboard(Request $request, $user_id)
    {
        $bulanSekarang = Carbon::now()->format('m');
        $tahunSekarang = Carbon::now()->format('Y');
        $nama = DB::table('user')->select('name')->where('user_id', $user_id)->first();


        $top = DB::select("SELECT 
    (SELECT SUM(total) 
     FROM transaksi a 
     JOIN jenis b ON a.jenis_id = b.jenis_id 
     WHERE b.tipe = 'pemasukan' 
       AND a.user_id = '$user_id'
       AND DATE_FORMAT(a.tanggal, '%Y-%m') = '$tahunSekarang-$bulanSekarang') AS total_pemasukan,

    (SELECT SUM(total) 
     FROM transaksi a 
     JOIN jenis b ON a.jenis_id = b.jenis_id 
     WHERE b.tipe = 'pengeluaran' 
       AND a.user_id = '$user_id'
       AND DATE_FORMAT(a.tanggal, '%Y-%m') = '$tahunSekarang-$bulanSekarang') AS total_pengeluaran,

    (SELECT c.batas
     FROM budget as c
     WHERE c.users_id = '$user_id'
       AND c.bulan = $bulanSekarang) AS bulanan,

    -- Menambahkan kolom persentase penggunaan tanpa angka desimal
    CASE
        WHEN (SELECT c.batas
              FROM budget as c
              WHERE c.users_id = '$user_id'
                AND c.bulan = $bulanSekarang) IS NOT NULL
        THEN
            ROUND(
                (SELECT SUM(total)
                 FROM transaksi a 
                 JOIN jenis b ON a.jenis_id = b.jenis_id 
                 WHERE b.tipe = 'pengeluaran' 
                   AND a.user_id = '$user_id'
                   AND DATE_FORMAT(a.tanggal, '%Y-%m') = '$tahunSekarang-$bulanSekarang') / 
                (SELECT c.batas
                 FROM budget as c
                 WHERE c.users_id = '$user_id'
                   AND c.bulan = $bulanSekarang) * 100
            )
        ELSE 0
    END AS persen_penggunaan
");


        // $persen = ($top[0]->total_pengeluaran / $top[0]->bulanan) * 100;


        $recent = DB::select("SELECT a.nama,a.tanggal,a.total,c.icon,d.warna,b.tipe FROM transaksi as a 
                    JOIN jenis as b on a.jenis_id = b.jenis_id and a.user_id  = b.user_id
                    join icons as c on b.icon = c.id
                    JOIN warnas as d on b.warna = d.warna_id
                    where a.user_id = ? 
                    ORDER BY a.tanggal DESC
                    LIMIT 5", [$user_id]);

        $goal = DB::select("SELECT 
                a.goal_id,
                a.nama,
                a.target,
                a.`status`,
                a.user_id,
                c.icon,
                d.warna,
                COALESCE(SUM(b.total), 0) AS total,
                CASE 
                    WHEN a.target > 0 THEN
                        FLOOR((COALESCE(SUM(b.total), 0) / a.target) * 100)
                    ELSE 0
                END AS progress_percentage
            FROM 
                goal AS a
            JOIN 
                icons AS c ON a.icon = c.id
            JOIN 
                warnas AS d ON a.warna = d.warna_id
            LEFT JOIN 
                transaksi AS b ON a.goal_id = b.goal_id AND a.user_id = b.user_id
            WHERE 
                a.user_id = ?
            GROUP BY
                a.goal_id, a.nama, a.target, a.`status`, a.user_id, c.icon, d.warna;", [$user_id]);

        $hutang = DB::select("SELECT a.*, (SELECT COALESCE(SUM(b.total), 0) 
                     FROM transaksi AS b 
                     WHERE a.hutang_id = b.hutang_id 
                    AND a.user_id = b.user_id) AS total,
                CASE 
                    WHEN a.target > 0 THEN
                        FLOOR((SELECT COALESCE(SUM(b.total), 0) 
                            FROM transaksi AS b 
                            WHERE a.hutang_id = b.hutang_id 
                                AND a.user_id = b.user_id) / a.target * 100)
                    ELSE 0 
                END AS progress_percentage FROM hutang AS a WHERE a.user_id = ?", [$user_id]);




        return response()->json([
            'nama' => $nama,
            'bulan' => bulanNama($bulanSekarang),
            'tahun' => $tahunSekarang,
            'top' => $top,
            'recent' => $recent,
            'goal' => $goal,
            'hutang' => $hutang,
        ]);
    }
}
