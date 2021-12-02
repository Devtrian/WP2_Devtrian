<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Autentifikasi extends CI_Controller
{
  //method untuk memanggil library form validation
  public function __construct()
  {
    parent::__construct();
    $this->load->library('form_validation');
  }
  public function index()
  {
    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
    $this->form_validation->set_rules('password', 'Password', 'trim|required');
    if ($this->form_validation->run() == false) {
      $data['title'] = 'Login';
      $this->load->view('tampilan/aute_header', $data);
      $this->load->view('autentifikasi/login');
      $this->load->view('tampilan/aute_footer');
    } else {
      $this->_login();
    }
  }

  private function _login()
  {
    $email = htmlspecialchars($this->input->post(
      'email',
      true
    ));
    $password = $this->input->post('password', true);
    $user = $this->ModelUser->cekData(['email' => $email])->row_array();
    //jika usernya ada
    if ($user) {
      //jika user sudah aktif
      if ($user['is_active'] == 1) {
        //cek password
        if (password_verify($password, $user['password'])) {
          $data = [
            'email' => $user['email'],
            'role_id' => $user['role_id']
          ];
          $this->session->set_userdata($data);
          if ($user['role_id'] == 1) {
            redirect('admin');
          } else {
            if ($user['image'] == 'default.jpg') {
              $this->session->set_flashdata(
                'pesan',
                '<div class="alert alert-info alert-message" role="alert">Silahkan
Ubah Profile Anda untuk Ubah Photo Profil</div>'
              );
            }
            redirect('user');
          }
        } else {
          $this->session->set_flashdata('pesan', '<div
class="alert alert-danger alert-message" role="alert">Password
salah!!</div>');
          redirect('autentifikasi');
        }
      } else {
        $this->session->set_flashdata('pesan', '<div
class="alert alert-danger alert-message" role="alert">User belum
diaktifasi!!</div>');
        redirect('autentifikasi');
      }
    } else {
      $this->session->set_flashdata('pesan', '<div
class="alert alert-danger alert-message" role="alert">Email tidak
terdaftar!!</div>');
      redirect('autentifikasi');
    }
  }

  public function registrasi()
  {
    $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
    $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
      'is_unique' => 'Email ini sudah digunakan !'
    ]);
    $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
      'matches' => 'Password tidak sama !',
      'min_length' => 'Password harus lebih dari 3 karakter!'
    ]);
    $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

    if ($this->form_validation->run() == false) {
      $data['title'] = 'Daftar Akun';
      $this->load->view('tampilan/aute_header', $data);
      $this->load->view('autentifikasi/registrasi');
      $this->load->view('tampilan/aute_footer');
    } else {
      $data = [
        'nama' => htmlspecialchars($this->input->post('nama', true)),
        'email' => htmlspecialchars($this->input->post('email', true)),
        'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
        'image' => 'default.jpg',
        'role_id' => 1,
        'is_active' => 1,
        'tanggal_input' => time()
      ];
      $this->db->insert('user', $data);
      $this->session->set_flashdata('pesan', '<div class="alert alert-success" role="alert">Daftar Akun Berhasil !</div>');
      redirect('autentifikasi');
    }
  }


  public function logout()
  {
    $this->session->unset_userdata('email');
    $this->session->unset_userdata('role_id');
    $this->session->set_flashdata('pesan', '<div class="alert alert-success" role="alert">Anda Telah Logout </div>');
    redirect('autentifikasi');
  }
}
