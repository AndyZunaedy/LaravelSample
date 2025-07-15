<?php

namespace App\Http\Repository;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class UsersRepository
{

    public function GetUserLogin($username, $password)
    {
        $ch = curl_init();

        // --- ENDPOINT LOGIN --- //
        $ArrayLoginPut = array(
            "password" => $password,
            "type" => "PUT",
            "username" => $username,
        );
        $BodyLoginPut = json_encode($ArrayLoginPut);
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'API',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $BodyLoginPut,
        ));
        $jsonLoginPut = curl_exec($ch);
        $ResultLoginPut = json_decode($jsonLoginPut);
        $DataUser = $ResultLoginPut->data;
        $TokenLogin = $ResultLoginPut->token;
        $result = array();
        $result[0] = $DataUser;
        $ArraySession = array();
        // --- ENDPOINT LOGIN --- //
        // --- ENDPOINT GROUP MAPPING --- //
        $headers = array(
            "Accept: application/json",
            "Authorization: " . $TokenLogin,
        );
        $UnitKerja = $result[0]->Unit_Kerja;
        if ($UnitKerja != '') {
            $ch_access = curl_init();
            curl_setopt_array($ch_access, array(
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_URL => 'API',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $jsonGroupMapping = curl_exec($ch_access);
            $ResultDecodenGroupMapping = json_decode($jsonGroupMapping);
            $dataGroupMapping = $ResultDecodenGroupMapping->data->JUMLAH;
            curl_close($ch_access);
        }

        // --- ENDPOINT GROUP MAPPING --- //

        // --- If untuk role KC dan SAO --- //
        $RoleLogin = $result[0]->Role;
        $CountGroupMapping = '';
        if ($RoleLogin == '4' || $RoleLogin == '2') {
            $CountGroupMapping = $dataGroupMapping;
        } else {
            $CountGroupMapping = false;
        }
        // --- If untuk role KC dan SAO --- //

        foreach ($result as $i => $dt) {
            $ArraySession[$i]['errorCode'] = $ResultLoginPut->status;
            $ArraySession[$i]['messageError'] = $ResultLoginPut->message;
            $ArraySession[$i]['username'] = $dt->Username;
            $ArraySession[$i]['nama'] = $dt->Nama;
            $ArraySession[$i]['nama_depan'] = $dt->Jabatan;
            $ArraySession[$i]['Password'] = $dt->Password;
            $ArraySession[$i]['Initial'] = $dt->Initial;
            $ArraySession[$i]['jabatan'] = $dt->Jabatan;
            $ArraySession[$i]['nama_belakang'] = $dt->Nama_Belakang;
            $ArraySession[$i]['ChangePasswordDate'] = $dt->ChangePasswordDate;
            $ArraySession[$i]['unit_kerja'] = $dt->Unit_Kerja;
            $ArraySession[$i]['CanSync'] = $dt->CanSync;
            $ArraySession[$i]['BranchName'] = $dt->Nama_Cabang;
            $ArraySession[$i]['Status'] = $dt->status;
            $ArraySession[$i]['Is_Branch'] = $dt->Is_Branch;
            $ArraySession[$i]['Is_Approval'] = $dt->Is_Approval;
            $ArraySession[$i]['Is_Approval2'] = $dt->Is_Approval2;
            $ArraySession[$i]['Is_Approval_KA'] = $dt->Is_Approval_KA;
            $ArraySession[$i]['Is_View'] = $dt->Is_View;
            $ArraySession[$i]['Is_Update'] = $dt->Is_Update;
            $ArraySession[$i]['Is_Download'] = $dt->Is_Download;
            $ArraySession[$i]['Is_Delete'] = $dt->Is_Delete;
            $ArraySession[$i]['Token'] = $TokenLogin;
            $ArraySession[$i]['Role'] = $dt->Role;
            $ArraySession[$i]['Foto_karyawan'] = $dt->foto_karyawan;
            $ArraySession[$i]['JumlahCabang'] = $CountGroupMapping;
        }
        curl_close($ch);
        Session::put('user', $ArraySession);
        return $ArraySession;
    }


    public function GetGroupMapping(Request $request)
    {
        if ($request->session()->has('SESS_USER_NAME')) {
            $Role = Session::get('SESS_USER_NAME')['user']['Role'];
            $Unit = Session::get('SESS_USER_NAME')['user']['unit_kerja'];
            $Token = Session::get('SESS_USER_NAME')['user']['Token'];
            $headers = array(
                "Accept: application/json",
                "Authorization: " . $Token,
            );
            $dataGroupMapping = '';

            if ($Role == '4' || $Role == '2') {
                $ch_access = curl_init();
                curl_setopt_array($ch_access, array(
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_URL => 'API',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));
                $jsonGroupMapping = curl_exec($ch_access);
                $ResultDecodenGroupMapping = json_decode($jsonGroupMapping);
                $dataGroupMapping = $ResultDecodenGroupMapping->data->JUMLAH;
                curl_close($ch_access);
            } else {
                $dataGroupMapping = '0';
            }
            return $dataGroupMapping;
        } else {
            return view('home.home');
        }
    }

    public function GetUnit(Request $request)
    {
        if ($request->session()->has('SESS_USER_NAME')) {

            $Jabatan = Session::get('SESS_USER_NAME')['user']['jabatan'];
            $UnitKerja = Session::get('SESS_USER_NAME')['user']['unit_kerja'];
            $Token = Session::get('SESS_USER_NAME')['user']['Token'];
            $headers = array(
                "Accept: application/json",
                "Authorization: " . $Token,
            );

            $ch_access = curl_init();
            curl_setopt_array($ch_access, array(
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_URL => 'API',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $jsonUnit = curl_exec($ch_access);
            $ResultDecodeUnit = json_decode($jsonUnit);
            $dataGetUnit = $ResultDecodeUnit->data;
            curl_close($ch_access);
            return $dataGetUnit;
        } else {
            Session::flash('error_message', 'Mohon Login terlebih dahulu.');
            return Redirect::to('/');
        }
    }

    public function UploadS3($foto)
    {
        $request_s3 = array();
        $bodydata_s3 = json_encode($request_s3);
        if ($foto != null) {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => 'API',
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $bodydata_s3,
            ));
            $server_output = curl_exec($ch);
            $path = json_decode($server_output);
            $pathFoto = $path->responseDescription;
            curl_close($ch);
        }
        return $pathFoto;
    }

    public function GetBrnetOpsDate(Request $request, $OurBranchID)
    {
        if ($request->session()->has('SESS_USER_NAME')) {

            $Token = Session::get('SESS_USER_NAME')['user']['Token'];
            $headers = array(
                "Accept: application/json",
                "Authorization: " . $Token,
            );
            $ch_access = curl_init();
            curl_setopt_array($ch_access, array(
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_URL => 'API',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $jsonGetBrnetOpsDate = curl_exec($ch_access);
            $ResultDecodeGetBrnetOpsDate = json_decode($jsonGetBrnetOpsDate);
            $dataGetGetBrnetOpsDate = $ResultDecodeGetBrnetOpsDate->data;
            curl_close($ch_access);
            return $dataGetGetBrnetOpsDate;
        } else {
            Session::flash('error_message', 'Mohon Login terlebih dahulu.');
            return Redirect::to('/');
        }
    }

    public function GetUserMobile()
    {

        $Token = Session::get('SESS_USER_NAME')['user']['Token'];
        $Unit = Session::get('SESS_USER_NAME')['user']['unit_kerja'];
        $Jabatan = Session::get('SESS_USER_NAME')['user']['jabatan'];
        $headers = array(
            "Accept: application/json",
            "Authorization: " . $Token,
        );
        $ArrayGetUserMobile = array();
        $BodyGetUserMobile = json_encode($ArrayGetUserMobile);
        $ch_access = curl_init();
        curl_setopt_array($ch_access, array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_URL => 'API',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $BodyGetUserMobile,

        ));
        $jsonGetUserMobile = curl_exec($ch_access);
        $ResultDecodeGetUserMobile = json_decode($jsonGetUserMobile);
        $dataGetGetUserMobile = $ResultDecodeGetUserMobile->data;
        curl_close($ch_access);
        return $dataGetGetUserMobile;
    }
}
