<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use App\Models\Admission\Form;
use App\Models\Admission\Setting;
use App\Models\Admission\Student;
use App\Models\Master\School;
use Carbon\Carbon;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FrontedController extends Controller
{
    public $data;

    public function __construct()
    {
        $this->data['school'] = School::first();
        $this->data['setting'] = new Setting();
    }

    public function home()
    {
        $this->data['title'] = 'Dashboard';
        return view('admission.fronted.home', $this->data);
    }

    public function register(Request $request)
    {
        if ($request->isMethod('post')){
            if ($request->_type == 'store'){
                $msg = $this->store($request);
            }
            elseif ($request->_type == 'update'){
                $msg = $this->update($request);
            }
            elseif ($request->_type == 'login'){
                $msg = $this->login($request);
            }
            elseif ($request->_type == 'logout'){
                $msg = $this->logout();
            }
            elseif ($request->_type == 'print')
            {
                return $this->print($request);
            }
            return response()->json($msg);
        }
        else {
            if (Session::has('auth')){
                $student = Session::get('auth');
                $student = Student::find($student->student_id);
                $checker = $student->toArray();
                $key = array_keys($checker);
                $compelete = [];
                for ($i=0;$i<count($checker);$i++){
                    if ($checker[$key[$i]] == null){
                        $compelete[] = [$key[$i] => $checker[$key[$i]]];
                    }
                }

                $this->data['student'] = $student;
                $this->data['compelete'] = count($compelete) >= 8 ? false : true;
                return view('admission.fronted.register_detail', $this->data);
            }
            else {
                return view('admission.fronted.register', $this->data);
            }
        }
    }

    public function authenticate($uuid)
    {
        $form = Form::where('form_uuid', $uuid)->first();
        return view('admission.fronted.authenticate', compact('form'));
    }

    public function term()
    {
        return view('admission.fronted.term');
    }

    public function store(Request $request)
    {
        try {
            if ($request->check_agreement == null){
                throw new \Exception('Silahkan centang persetujuan terlebih dahulu.');
            }
            else {
                if ($request->student_nisn != null && Student::where('student_nisn', $request->student_nisn)->count() >= 1){
                    throw new \Exception('NISN telah terdaftar, silahkan melengkapi pendaftaran.');
                }
                elseif (Student::where('student_nik', $request->student_nik)->count() >= 1){
                    throw new \Exception('NIK telah terdaftar, silahkan melengkapi pendaftaran.');
                }
                else {
                    $validator = Validator::make($request->all(), [
                        'student_name' => 'required|max:200',
                        'student_nisn' => 'nullable|min:10|max:10',
                        'student_nik' => 'required|min:16|max:16',
                        'student_birthplace' => 'required',
                        'student_birthday' => 'required|date_format:d/m/Y',
                        'student_gender' => 'required',
                        'student_religion' => 'required',
                        'student_siblingplace' => 'required',
                        'student_sibling' => 'required',
                        'student_civic' => 'required',
                        'student_hobby' => 'required',
                        'student_purpose' => 'required',
                        'student_email' => 'nullable|email',
                        'student_phone' => 'required',
                        'student_residence' => 'required',
                        'student_address' => 'required|string|max:200',
                        'student_province' => 'required',
                        'student_distric' => 'required',
                        'student_subdistric' => 'required',
                        'student_village' => 'required',
                        'student_distance' => 'required',
                        'student_transport' => 'required',
                        'student_travel' => 'required',
                        'student_program' => 'required',
                    ],
                        [
                            'student_name.required' => 'Kolom nama lengkap tidak boleh kosong.',
                            'student_name.max' => 'Panjang karakter maksimal 200 huruf.',
                            'student_nisn.min' => 'NISN salah, pastikan berjumlah 10 digit.',
                            'student_nisn.max' => 'NISN salah, pastikan berjumlah 10 digit.',
                            'student_nik.required' => 'NIK salah, pastikan berjumlah 16 digit.',
                            'student_nik.min' => 'NIK salah, pastikan berjumlah 16 digit.',
                            'student_nik.max' => 'NIK salah, pastikan berjumlah 16 digit.',
                            'student_birthplace.required' => 'Kolom tempat lahir tidak boleh kosong.',
                            'student_birthday.required' => 'Kolom tanggal lahir tidak boleh kosong.',
                            'student_gender.required' => 'Kolom jenis kelamin tidak boleh kosong, silahkan pilih.',
                            'student_religion.required' => 'Kolom agama tidak boleh kosong, silahkan pilih.',
                            'student_siblingplace.required' => 'Kolom anak-ke tidak boleh kosong.',
                            'student_sibling.required' => 'Kolom jumlah saudara tidak boleh kosong.',
                            'student_civic.required' => 'Kolom kewarganegaraan tidak boleh kosong, silahkan pilih.',
                            'student_hobby.required' => 'Kolom Hobi tidak boleh kosong, silahkan pilih.',
                            'student_purpose.required' => 'Kolom cita-cita tidak boleh kosong, silahkan pilih.',
                            'student_phone.required' => 'Kolom nomor HP tidak boleh kosong.',
                            'student_residence.required' => 'Kolom status tempat tinggal tidak boleh kosong, silahkan pilih.',
                            'student_address.required' => 'Kolom alamat tidak boleh kosong.',
                            'student_province.required' => 'Kolom propinsi tidak boleh kosong, silahkan pilih.',
                            'student_distric.required' => 'Kolom kabupaten tidak boleh kosong, silahkan pilih.',
                            'student_subdistric.required' => 'Kolom kecamatan tidak boleh kosong, silahkan pilih.',
                            'student_village.required' => 'Kolom kelurahan/desa tidak boleh kosong, silahkan pilih.',
                            'student_distance.required' => 'Kolom jarak tempat tinggal tidak boleh kosong, silahkan pilih.',
                            'student_transport.required' => 'Kolom transportasi tidak boleh kosong, silahkan pilih.',
                            'student_travel.required' => 'Kolom waktu tempuh tidak boleh kosong, silahkan pilih.',
                            'student_program.required' => 'Kolom program pilihan tidak boleh kosong, silahkan pilih.',
                        ]);
                    if ($validator->fails()){
                        throw new \Exception(Arr::first(Arr::flatten($validator->getMessageBag()->get('*'))));
                    }
                    else {
                        $student = new Student();
                        $student->student_name = $request->student_name;
                        $student->student_nisn = $request->student_nisn;
                        $student->student_nik = $request->student_nik;
                        $student->student_birthplace = $request->student_birthplace;
                        $student->student_birthday = Carbon::createFromFormat('d/m/Y', $request->student_birthday)->format('Y-m-d');
                        $student->student_gender = $request->student_gender;
                        $student->student_religion = $request->student_religion;
                        $student->student_siblingplace = $request->student_siblingplace;
                        $student->student_sibling = $request->student_sibling;
                        $student->student_civic = $request->student_civic;
                        $student->student_hobby = $request->student_hobby;
                        $student->student_purpose = $request->student_purpose;
                        $student->student_email = $request->student_email;
                        $student->student_phone = $request->student_phone;
                        $student->student_residence = $request->student_residence;
                        $student->student_address = $request->student_address;
                        $student->student_province = $request->student_province;
                        $student->student_distric = $request->student_distric;
                        $student->student_subdistric = $request->student_subdistric;
                        $student->student_village = $request->student_village;
                        $student->student_postal = $request->student_postal;
                        $student->student_distance = $request->student_distance;
                        $student->student_transport = $request->student_transport;
                        $student->student_travel = $request->student_travel;
                        $student->student_program = $request->student_program;
                        $student->student_boarding = $request->student_boarding;
                        $student->student_im_hepatitis = $request->student_im_hepatitis;
                        $student->student_im_polio = $request->student_im_polio;
                        $student->student_im_bcg = $request->student_im_bcg;
                        $student->student_im_campak = $request->student_im_campak;
                        $student->student_im_dpt = $request->student_im_dpt;
                        $student->student_im_covid = $request->student_im_covid;
                        if ($student->save()) {
                            $number_form        = Form::latest('form_letter')->first() == null ? 1 : Form::latest('form_letter')->first()->form_letter + 1;
                            $month              = ['01' => 'I', '02' => 'II', '03' => 'III', '04' => 'IV', '05' => "V", '06' => 'VI', '07' => 'VII', '08' => 'VIII', '09' => 'IX', '10' => 'X', '11' => 'XI', '12' =>'XII'];
                            $now                = Carbon::now();
                            $form               = new Form();
                            $form->form_uuid    = Str::uuid();
                            $form->form_letter  = $form->form_letter = sprintf("%04s", $form->form_letter) . '/PPDB.'.$this->data['school']->school_nickname.'/PP.006/'.$month[$now->format('m')].'/'.$now->format('Y');
                            $form->form_student = $student->student_id;
                            $form->form_date    = Carbon::now()->format('Y-m-d');
                            $form->form_count   = 0;
                            $form->save();
                            $path = storage_path('app/public/admission/fronted/images/student/' . $student->student_nik . '_qrcode.png');
                            QrCode::size(110)
                                ->format('png')->generate(route('admission.authenticate', $form->form_uuid), $path);
                            $msg = ['title' => 'Berhasil !', 'class' => 'success', 'text' => 'Pendaftaran berhasil, silahkan masuk menggunakan NIS/NIK & Tanggal Lahir untuk melengkapi pendfataran.'];
                        }
                    }
                }
            }
        }
        catch (\Exception $e){
            $msg = ['title' => 'Kesalahan !', 'class' => 'danger', 'text' => $e->getMessage()];
        }
        return $msg;
    }

    public function update(Request $request)
    {
        $form = [
            'student_name'          => 'required|max:200',
            'student_nisn'          => 'nullable|min:10|max:10',
            'student_nik'           => 'required|min:16|max:16',
            'student_birthplace'    => 'required',
            'student_birthday'      => 'required|date_format:d/m/Y',
            'student_gender'        => 'required',
            'student_religion'      => 'required',
            'student_siblingplace'  => 'required',
            'student_sibling'       => 'required',
            'student_civic'         => 'required',
            'student_hobby'         => 'required',
            'student_purpose'       => 'required',
            'student_email'         => 'required',
            'student_phone'         => 'required',
            'student_residence'     => 'required',
            'student_address'       => 'required|max:200',
            'student_province'      => 'required',
            'student_distric'       => 'required',
            'student_subdistric'    => 'required',
            'student_village'       => 'required',
            'student_distance'      => 'required',
            'student_transport'     => 'required',
            'student_travel'        => 'required',
            'student_program'       => 'required',
            'student_no_kk'         => 'required|min:16|max:16',
            'student_head_family'   => 'required',
            'student_guard_name'   => 'required',
            'student_guard_birthplace' => 'required',
            'student_guard_birthday' => 'required',
            'student_guard_nik' => 'required|min:16|max:16',
            'student_guard_study' => 'required',
            'student_guard_job' => 'required',
            'student_guard_earning' => 'required',
            'student_guard_phone' => 'required',
            'student_home_owner' => 'required',
            'student_home_address' => 'required',
            'student_home_postal' => 'required',
            'student_home_province' => 'required',
            'student_home_distric' => 'required',
            'student_home_subdistric' => 'required',
            'student_home_village' => 'required',
            'student_school_from' => 'required',
            'student_school_name' => 'required',
            'student_school_npsn' => 'required',
            'student_school_address' => 'required',
            'student_swaphoto' => 'mimes:jpg,jpeg|max:600',
            'student_ktp_photo' => 'mimes:jpg,jpeg|max:600',
            'student_akta_photo' => 'mimes:jpg,jpeg|max:600',
            'student_kk_photo' => 'mimes:jpg,jpeg|max:600',
            'student_ijazah_photo' => 'mimes:jpg,jpeg|max:600',
            'student_skhun_photo' => 'mimes:jpg,jpeg|max:600',
            'student_sholarship_photo' => 'mimes:jpg,jpeg|max:600',
        ];
        if (in_array($request->student_father_status, [2, 3])){
            $father = [
                'student_father_name'   => 'nullable',
                'student_father_birthplace' => 'nullable',
                'student_father_birthday' => 'nullable',
                'student_father_status' => 'nullable',
                'student_father_nik' => 'nullable|min:16|max:16',
                'student_father_study' => 'nullable',
                'student_father_job' => 'nullable',
                'student_father_earning' => 'nullable',
                'student_father_phone' => 'nullable',
            ];
        }
        else {
            $father = [
                'student_father_name'   => 'required',
                'student_father_birthplace' => 'required',
                'student_father_birthday' => 'required',
                'student_father_status' => 'required',
                'student_father_nik' => 'required|min:16|max:16',
                'student_father_study' => 'required',
                'student_father_job' => 'required',
                'student_father_earning' => 'required',
                'student_father_phone' => 'required',
            ];
        }
        if (in_array($request->student_mother_status, [2, 3])){
            $mother = [
                'student_mother_name'   => 'nullable',
                'student_mother_birthplace' => 'nullable',
                'student_mother_birthday' => 'nullable',
                'student_mother_status' => 'nullable',
                'student_mother_nik' => 'nullable|min:16|max:16',
                'student_mother_study' => 'nullable',
                'student_mother_job' => 'nullable',
                'student_mother_earning' => 'nullable',
                'student_mother_phone' => 'nullable',
            ];
        }
        else {
            $mother = [
                'student_mother_name'   => 'required',
                'student_mother_birthplace' => 'required',
                'student_mother_birthday' => 'required',
                'student_mother_status' => 'required',
                'student_mother_nik' => 'required|min:16|max:16',
                'student_mother_study' => 'required',
                'student_mother_job' => 'required',
                'student_mother_earning' => 'required',
                'student_mother_phone' => 'required',
            ];
        }
        $form = array_merge($form, $father);
        $form = array_merge($form, $mother);
        $message = [
            'student_name.required'          => 'Kolom nama lengkap tidak boleh kosong.',
            'student_name.max'          => 'Kolom nama lengkap maksimal 200 karakter',
            'student_nisn.min'          => 'NISN salah, silahkan periksa kembali',
            'student_nisn.max'          => 'NISN salah, silahkan periksa kembali',
            'student_nik.required'           => 'Kolom NIK tidak boleh kosong.',
            'student_nik.min'           => 'NIK salah, silahkan periksa kembali.',
            'student_nik.max'           => 'NIK salah, silahkan periksa kembali.',
            'student_birthplace.required'    => 'Kolom tempat lahir tidak boleh kosong.',
            'student_birthday.required'      => 'Kolom tanggal lahir tidak boleh kosong.',
            'student_gender.required'        => 'Kolom jenis kelamin tidak boleh kosong, silahkan memilih.',
            'student_religion.required'      => 'Kolom agama tidak boleh kosong, silahkan memilih.',
            'student_siblingplace.required'  => 'Kolom anak-ke tidak boleh kosong.',
            'student_sibling.required'       => 'Kolom jumlah saudara tidak boleh kosong.',
            'student_civic.required'         => 'Kolom kewarganegaraan tidak boleh kosong, silahkan memilih.',
            'student_hobby.required'         => 'Kolom hobi tidak boleh kosong, silahkan memilih.',
            'student_purpose.required'       => 'Kolom cita-cita tidak boleh kosong, silahkan memilih.',
            'student_email.required'         => 'Kolom email tidak boleh kosong.',
            'student_phone.required'         => 'Kolom nomor HP tidak boleh kosong.',
            'student_residence.required'     => 'Kolom jenis tempat tinggak siswa tidak boleh kosong, silahkan memilih.',
            'student_address.required'       => 'Kolom alamat tidak boleh kosong.',
            'student_address.max'       => 'Kolom alamat maksimal 200 karakter',
            'student_province.required'      => 'Kolom propinsi tidak boleh kosong, silahkan memilih.',
            'student_distric.required'       => 'Kolom kabupaten tidak boleh kosong, silahkan memilih.',
            'student_subdistric.required'    => 'Kolom kecamatan tidak boleh kosong, silahkan memilih.',
            'student_village.required'       => 'Kolom kelurahan/desa tidak boleh kosong, silahkan memilih.',
            'student_distance.required'      => 'Kolom jarak tempat tinggal tidak boleh kosong, silahkan memilih.',
            'student_transport.required'     => 'Kolom tranportasi yang dipakai tidak boleh kosong, silahkan memilih.',
            'student_travel.required'        => 'Kolom waktu tempuh tidak boleh kosong, silahkan memilih.',
            'student_program.required'       => 'Kolom program pilihan tidak boleh kosong, silahkan memilih.',
            'student_no_kk.required'         => 'Kolom nomor kartu keluarga tidak boleh kosong.',
            'student_no_kk.min'         => 'Nomor kartu keluarga salah, silahkan periksa kembali.',
            'student_no_kk.max'         => 'Nomor kartu keluarga salah, silahkan periksa kembali.',
            'student_head_family.required'   => 'Kolom kepala keluarga tidak boleh kosong.',
            'student_father_name.required'   => 'Kolom nama ayah kandung tidak boleh kosong.',
            'student_father_birthplace.required' => 'Kolom tempat lahir ayah tidak boleh kosong.',
            'student_father_birthday.required' => 'Kolom tanggal lahir ayah tidak boleh kosong.',
            'student_father_status.required' => 'Kolom status ayah tidak boleh kosong, silahkan memilih.',
            'student_father_nik.required' => 'Kolom NIK ayah tidak boleh kosong.',
            'student_father_nik.min' => 'NIK ayah salah, silahkan periksa kembali.',
            'student_father_nik.max' => 'NIK ayah salah, silahkan periksa kembali.',
            'student_father_study' => 'Kolom pendidikan ayah tidak boleh kosong, silahkan memilih.',
            'student_father_job' => 'Kolom pekerjaan ayah tidak boleh kosong, silahkan memilih.',
            'student_father_earning' => 'Kolom pendapatan ayah tidak boleh kosong, silahkan memilih.',
            'student_father_phone' => 'Kolom nomor HP ayah tidak boleh kosong',
            'student_mother_name.required'   => 'Kolom nama ibu kandung tidak boleh kosong.',
            'student_mother_birthplace.required' => 'Kolom tempat lahir ibu tidak boleh kosong.',
            'student_mother_birthday.required' => 'Kolom tanggal lahir ibu tidak boleh kosong.',
            'student_mother_status.required' => 'Kolom status ibu tidak boleh kosong, silahkan memilih.',
            'student_mother_nik.required' => 'Kolom NIK ibu tidak boleh kosong.',
            'student_mother_nik.min' => 'NIK ibu salah, silahkan periksa kembali.',
            'student_mother_nik.max' => 'NIK ibu salah, silahkan periksa kembali.',
            'student_mother_study' => 'Kolom pendidikan ibu tidak boleh kosong, silahkan memilih.',
            'student_mother_job' => 'Kolom pekerjaan ibu tidak boleh kosong, silahkan memilih.',
            'student_mother_earning' => 'Kolom pendapatan ibu tidak boleh kosong, silahkan memilih.',
            'student_mother_phone' => 'Kolom nomor HP ibu tidak boleh kosong',
            'student_guard_name.required'   => 'Kolom nama wali kandung tidak boleh kosong.',
            'student_guard_birthplace.required' => 'Kolom tempat lahir wali tidak boleh kosong.',
            'student_guard_birthday.required' => 'Kolom tanggal lahir wali tidak boleh kosong.',
            'student_guard_nik.required' => 'Kolom NIK wali tidak boleh kosong.',
            'student_guard_nik.min' => 'NIK wali salah, silahkan periksa kembali.',
            'student_guard_nik.max' => 'NIK wali salah, silahkan periksa kembali.',
            'student_guard_study.required' => 'Kolom pendidikan wali tidak boleh kosong, silahkan memilih.',
            'student_guard_job.required' => 'Kolom pekerjaan wali tidak boleh kosong, silahkan memilih.',
            'student_guard_earning.required' => 'Kolom pendapatan wali tidak boleh kosong, silahkan memilih.',
            'student_guard_phone.required' => 'Kolom nomor HP wali tidak boleh kosong.',
            'student_home_owner.required' => 'Kolom status kepemilikan rumah tidak boleh kosong, silahkan memilih.',
            'student_home_address.required' => 'Kolom alamat rumah orangtua tidak boleh kosong.',
            'student_home_postal.required' => 'Kolom kodepos rumah orangtua tidak boleh kosong.',
            'student_home_province.required' => 'Kolom propinsi rumah orangtua tidak boleh kosong, silahkan memilih.',
            'student_home_distric.required' => 'Kolom kabupaten rumah orangtua tidak boleh kosong, silahkan memilih.',
            'student_home_subdistric.required' => 'Kolom kecamatan rumah orangtua tidak boleh kosong, silahkan memilih.',
            'student_home_village.required' => 'Kolom kelurahan/desa rumah orangtua tidak boleh kosong, silahkan memilih.',
            'student_school_from.required' => 'Kolom jenis sekolah asal tidak boleh kosong.',
            'student_school_name.required' => 'Kolom nama sekolah asal tidak boleh kosong',
            'student_school_address.required' => 'Kolom alamat sekolah asal tidak boleh kosong.',
            'student_swaphoto.mimes' => ' Gambar swafoto harus berformat jpg/jpeg.',
            'student_swaphoto.max' => 'Ukuran gambar swafoto maksimal 512Kb',
            'student_ktp_photo.mimes' => ' Gambar KTP harus berformat jpg/jpeg.',
            'student_ktp_photo.max' => 'Ukuran gambar KTP maksimal 512Kb',
            'student_akta_photo.mimes' => ' Gambar akta kelahiran harus berformat jpg/jpeg.',
            'student_akta_photo.max' => 'Ukuran gambar akta kelahiran maksimal 512Kb',
            'student_kk_photo.mimes' => ' Gambar kartu keluarga harus berformat jpg/jpeg.',
            'student_kk_photo.max' => 'Ukuran gambar kartu kelahiran maksimal 512Kb',
            'student_ijazah_photo.mimes' => ' Gambar ijazah harus berformat jpg/jpeg.',
            'student_ijazah_photo.max' => 'Ukuran gambar ijazah maksimal 512Kb',
            'student_skhun_photo.mimes' => ' Gambar SKHUN/SKL harus berformat jpg/jpeg.',
            'student_skhun_photo.max' => 'Ukuran gambar SKHUN/SKL maksimal 512Kb',
            'student_sholarship_photo.mimes' => ' Gambar Kartu Bantuan harus berformat jpg/jpeg.',
            'student_sholarship_photo.max' => 'Ukuran gambar Kartu Bantuan maksimal 512Kb',
        ];
        try {
            $validator = Validator::make($request->all(), $form, $message);
            if ($validator->fails()){
                throw new \Exception(Arr::first(Arr::flatten($validator->getMessageBag()->get('*'))));
            }
            else{
                $student = Student::find($request->student_id);
                $student->student_name          = $request->student_name;
                $student->student_nisn          = $request->student_nisn;
                $student->student_nik           = $request->student_nik;
                $student->student_birthplace    = $request->student_birthplace;
                $student->student_birthday      = Carbon::createFromFormat('d/m/Y', $request->student_birthday)->format('Y-m-d');
                $student->student_gender        = $request->student_gender;
                $student->student_religion      = $request->student_religion;
                $student->student_siblingplace  = $request->student_siblingplace;
                $student->student_sibling       = $request->student_sibling;
                $student->student_civic         = $request->student_civic;
                $student->student_hobby         = $request->student_hobby;
                $student->student_purpose       = $request->student_purpose;
                $student->student_email       = $request->student_email;
                $student->student_phone       = $request->student_phone;
                $student->student_residence     = $request->student_residence;
                $student->student_address       = $request->student_address;
                $student->student_province      = $request->student_province;
                $student->student_distric       = $request->student_distric;
                $student->student_subdistric    = $request->student_subdistric;
                $student->student_village       = $request->student_village;
                $student->student_postal        = $request->student_postal;
                $student->student_distance      = $request->student_distance;
                $student->student_transport     = $request->student_transport;
                $student->student_travel        = $request->student_travel;
                $student->student_program       = $request->student_program;
                $student->student_boarding       = $request->student_boarding;
                $student->student_im_hepatitis  = $request->student_im_hepatitis;
                $student->student_im_polio      = $request->student_im_polio;
                $student->student_im_bcg        = $request->student_im_bcg;
                $student->student_im_campak     = $request->student_im_campak;
                $student->student_im_dpt        = $request->student_im_dpt;
                $student->student_im_covid        = $request->student_im_covid;
                $student->student_no_kk         = $request->student_no_kk;
                $student->student_head_family   = $request->student_head_family;
                $student->student_father_name   = $request->student_father_name;
                $student->student_mother_name   = $request->student_mother_name;
                $student->student_father_birthplace   = $request->student_father_birthplace;
                $student->student_mother_birthplace   = $request->student_mother_birthplace;
                $student->student_father_birthday = Carbon::createFromFormat('d/m/Y', $request->student_father_birthday)->format('Y-m-d');
                $student->student_mother_birthday = Carbon::createFromFormat('d/m/Y', $request->student_mother_birthday)->format('Y-m-d');
                $student->student_father_status = $request->student_father_status;
                $student->student_mother_status = $request->student_mother_status;
                $student->student_father_nik = $request->student_father_nik;
                $student->student_mother_nik = $request->student_mother_nik;
                $student->student_father_study = $request->student_father_study;
                $student->student_mother_study = $request->student_mother_study;
                $student->student_father_job = $request->student_father_job;
                $student->student_mother_job = $request->student_mother_job;
                $student->student_father_earning = $request->student_father_earning;
                $student->student_mother_earning = $request->student_mother_earning;
                $student->student_father_phone = $request->student_father_phone;
                $student->student_mother_phone = $request->student_mother_phone;
                $student->student_guard_name   = $request->student_guard_name;
                $student->student_guard_birthplace   = $request->student_guard_birthplace;
                $student->student_guard_birthday = Carbon::createFromFormat('d/m/Y', $request->student_guard_birthday)->format('Y-m-d');
                $student->student_guard_nik = $request->student_guard_nik;
                $student->student_guard_study = $request->student_guard_study;
                $student->student_guard_job = $request->student_guard_job;
                $student->student_guard_earning = $request->student_guard_earning;
                $student->student_guard_phone = $request->student_guard_phone;
                $student->student_home_owner = $request->student_home_owner;
                $student->student_home_address = $request->student_home_address;
                $student->student_home_postal = $request->student_home_postal;
                $student->student_home_province = $request->student_home_province;
                $student->student_home_distric = $request->student_home_distric;
                $student->student_home_subdistric = $request->student_home_subdistric;
                $student->student_home_village = $request->student_home_village;
                $student->student_school_from = $request->student_school_from;
                $student->student_school_name = $request->student_school_name;
                $student->student_school_npsn = $request->student_school_npsn;
                $student->student_school_address = $request->student_school_address;
                $path = storage_path('app/public/admission/fronted/images/student/');
                if ($request->hasFile('student_swaphoto')){
                    $request->file('student_swaphoto')->move($path , $student->student_nik .'_swaphoto.jpg');
                    $student->student_swaphoto = 1;
                }
                if ($request->hasFile('student_ktp_photo')){
                    $request->file('student_ktp_photo')->move($path , $student->student_nik .'_ktp.jpg');
                    $student->student_ktp_photo = 1;
                }
                if ($request->hasFile('student_akta_photo')){
                    $request->file('student_akta_photo')->move($path , $student->student_nik .'_akta.jpg');
                    $student->student_akta_photo = 1;
                }
                if ($request->hasFile('student_kk_photo')){
                    $request->file('student_kk_photo')->move($path , $student->student_nik .'_kk.jpg');
                    $student->student_kk_photo = 1;
                }
                if ($request->hasFile('student_ijazah_photo')){
                    $request->file('student_ijazah_photo')->move($path , $student->student_nik .'_ijazah.jpg');
                    $student->student_ijazah_photo = 1;
                }
                if ($request->hasFile('student_skhun_photo')){
                    $request->file('student_skhun_photo')->move($path , $student->student_nik .'_skhun.jpg');
                    $student->student_skhun_photo = 1;
                }
                if ($request->hasFile('student_sholarship_photo')){
                    $request->file('student_sholarship_photo')->move($path , $student->student_nik .'_scholarship.jpg');
                    $student->student_sholarship_photo = 1;
                }

                if ($student->save()){
                    $msg = ['title' => 'Berhasil !', 'class' => 'success', 'text' => 'Pembaruhan data berhasil, silahkan melakukan cetak bukti pendaftaran dan pembayaran tagihan.'];
                }
            }
        }
        catch (\Exception $e){
            $msg = ['title' => 'Kesalahan !', 'class' => 'danger', 'text' => $e->getMessage()];
        }
        return $msg;
    }

    public function login(Request $request)
    {
        try {
            if ($request->student_nisn == null){
                throw new \Exception('Silahkan masukkan NISN/NIK & tanggal lahir');
            }
            else {
                if(Student::where('student_nisn', $request->student_nisn)->count() > 0 || Student::where('student_nik', $request->student_nisn)->count() > 0){
                    $student = Student::where('student_nisn', $request->student_nisn)->count() >= 1 ? Student::where('student_nisn', $request->student_nisn)->first() : Student::where('student_nik', $request->student_nisn)->first();
                    if ($student->birthday() == $request->student_birthday){
                        Session::put('auth', $student);
                        $msg = ['title' => 'Berhasil !', 'class' => 'success', 'text' => 'Berhasil masuk, anda akan di alihkan ke halaman lengkapi formulir dalam 2 detik.'];
                    }
                    else {
                        throw new \Exception('NISN & Tanggal lahir tidak tepat, silahkan periksa kembali.');
                    }
                }
                else {
                    throw new \Exception('Gagal masuk, NISN/NIK tidak di temukan.');
                }
            }
        }
        catch (\Exception $e){
            $msg = ['title' => 'Kesalahan !', 'class' => 'danger', 'text' => $e->getMessage()];
        }
        return $msg;
    }

    public function logout()
    {
        Session::forget('auth');
        return  ['title' => 'Berhasil !', 'class' => 'success', 'text' => 'Berhasil keluar, anda akan di arahkan ke halaman beranda dalam 2 detik.'];
    }

    public function print(Request $request)
    {
        $cert = 'file://'. realpath(storage_path('app/cert/selfcert.pem'));
        $key = 'file://'. realpath(storage_path('app/cert/enc_key.pem'));
        $info = array(
            'Name' => 'Kepala ' . $this->data['school']->name(false),
            'Reason' => 'Formulir PPDB TP. ' . $this->data['setting']->value('app_year'),
            'Location' => $this->data['school']->name(false),
            'ContactInfo' => $this->data['school']->school_email,
        );

        $student = Student::find($request->_data);
        $form = Form::where('form_student', $request->_data)->first();
        $this->data['student'] = $student;
        $this->data['form']     = $form;
        $view = View::make('admission.fronted.register_print', $this->data)->render();
        TCPDF::setSignature($cert, $key, 'myu2nnmd', '', 2, $info);
        TCPDF::SetFont('times', '', 12);
        TCPDF::SetMargins(1, 1, 1);
        TCPDF::SetAutoPageBreak(true, 0);
        TCPDF::AddPage();
        TCPDF::writeHTML($view, true, 0, true, 0, '');
        TCPDF::setSignatureAppearance(1, 8.3, 5.1, 4.1);
        TCPDF::Output('formulir-'. $student->student_nik .'.pdf');
        TCPDF::reset();
    }

    public function result(Request $request)
    {
        if ($request->isMethod('post')) {
            if($request->_type == 'data' && $request->_data == 'all'){
                $student = 0;
                for ($i=1;$i<13;$i++){
                    $all[] = $student = Student::whereMonth('created_at', $i)->count() > 0 ? Student::whereMonth('created_at', $i)->count() + $student: 0;
                }
                $data[] = array_merge(['Jumlah Pendaftar'], $all);
            }
            elseif ($request->_type == 'data' && $request->_data == 'gender'){
                $student = 0;
                for ($i=1;$i<3;$i++) {
                    for ($j = 1; $j < 13; $j++) {
                        $count[] = $student = Student::whereMonth('created_at', $j)
                            ->where('student_gender', $i)->count() > 0 ? Student::whereMonth('created_at', $j)
                                ->where('student_gender', $i)->count() + $student : 0;
                    }
                    $gender = $i == 1 ? array_merge(['Jumlah Laki-laki'], $count) : array_merge(['Jumlah Perempuan'], $count) ;
                    $count = [];
                    $student = 0;
                    $data[] = $gender;
                }
            }
            elseif ($request->_type == 'data' && $request->_data == 'program'){
                $student = 0;
                for ($i=1;$i<5;$i++) {
                    for ($j = 1; $j < 13; $j++) {
                        $count[] = $student = Student::whereMonth('created_at', $j)
                            ->where('student_program', $i)->count() > 0 ? Student::whereMonth('created_at', $j)
                                ->where('student_program', $i)->count() + $student : 0;
                    }
                    if ($i == 1){
                        $program = array_merge(['Tahfidz'], $count);
                    }
                    elseif ($i == 2){
                        $program = array_merge(['Sains & Bahasa'], $count);
                    }
                    elseif ($i == 3){
                        $program = array_merge(['Kitab Kuning'], $count);
                    }
                    elseif ($i == 4){
                        $program = array_merge(['Kelas Reguler'], $count);
                    }
                    $count = [];
                    $student = 0;
                    $data[] = $program;
                }
            }
            return response()->json($data);
        }
        else {
            return view('admission.fronted.result', $this->data);
        }
    }

    public function registrant(Request $request)
    {
        if ($request->isMethod('post')){
            if ($request->_type == 'data' && $request->_data == 'all') {
                $no = 1;
                foreach (Student::orderBy('created_at', 'ASC')->get() as $student) {
                    $data[] = [
                        $no++,
                        $student->student_name,
                        $student->student_nik,
                        $student->student_nisn,
                        $student->student_address,
                        $student->student_father_name,
                        $student->student_mother_name,
                    ];
                };
                $msg = ['data' => empty($data) ? [] : $data];
            }
            return response()->json($msg);
        }
        else {
            return view('admission.fronted.registrant', $this->data);
        }
    }
}
