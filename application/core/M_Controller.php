<?php

/**
 *
 */
class M_Controller extends Ci_Controller
{

    Private $metodo="AES-256-CBC";
    private  $secret_iv="101712";
    private  $secret_key="upqroo";
    //define('METHOD','AES-256-CBC');
    //define('SECRET_KEY','$CARLOS@2016');
    //define('SECRET_IV','101712');
    //
    public $status=false;
    //almacena el tipo del usuario logueado
    public $tipoUsario=0;
    //almacena el nombre del usuario logueado
    public $nombre='    ';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('session');
        //$this->load->model('m_model');
    }

    //Revisa el estado del usuario (logueado=true, noLogueado=false)
    public function checkLogin()
    {
        return $this->tipoUsario;
    }
        
    public function encryption($string){
        $output=FALSE;
        $key=hash('sha256', $this->config->item('encryption_key'));
        $iv=substr(hash('sha256', $this->secret_iv), 0, 16);
        $output=openssl_encrypt($string, $this->metodo, $key, 0, $iv);
        $output=base64_encode($output);
        return $output;
    }
    public function decryption($string){
        $key=hash('sha256', $this->config->item('encryption_key'));
        $iv=substr(hash('sha256', $this->secret_iv), 0, 16);
        $output=openssl_decrypt(base64_decode($string), $this->metodo, $key, 0, $iv);
        return $output;
    }

    public function Login($username,$password)
    {
        $this->load->model('AdminModel');
        $resultado=$this->AdminModel->login($username,$password);
        //var_dump($resultado);
        if(!empty($resultado))
        {
            $_SESSION['UserName']=$resultado[0]->usuario;
            $_SESSION['Userid']=$resultado[0]->id;
        }
        else
        {
            $resultado=null;
        }
        return $resultado;
    }

    public function Logout()
    {
        unset($_SESSION['UserName']);
        unset($_SESSION['Userid']);
    }
    
    //Carga las vistas publicas
    public function loadView($view,$data)
    {
        $this->load->view('templates/header',$data);
        $this->load->view($view,$data);
        $this->load->view('templates/footer');
    }


    //Carga la vista de administrador
    public function loadViewAdmin($view,$data)
    {
        $this->load->view('private/admin',$data);
        $this->load->view('private/'.$view,$data);
        //$this->load->view('templates/footer');
    }

    public function renameFileIfExist($nombre,$folder)
    {
        //return file_exists($folder.'/'.$nombre);
        //echo $nombre;
        $nNombre=substr($nombre,0,strlen($nombre)-4);
        $aux=substr($nombre,strlen($nNombre),strlen($nombre));
        if(is_dir($folder))
        {
            if(is_file($folder.'/'.$nombre))
            {
                $directorio = opendir($folder);
                $contRep=0;
                while ($archivo = readdir($directorio))
                {
                    $nArchivo=substr($archivo,0,strlen($archivo)-4);
                    //echo $nArchivo." ";
                    if($nArchivo==$nNombre)
                    {
                        $contRep++;
                    }
                }
                $nNombre.=$contRep;
                return $this->renameFileIfExist($nNombre.$aux,$folder);
            }
            else
            {
                $nNombre.=$aux;
            }
        }
        return $nNombre;
    }

    public function genereteRute($relaviteRute)
    {
        $rute="";
        if(!empty($relaviteRute) && $relaviteRute!="")
        {
            if (is_dir($relaviteRute))
            {
                $date=date('Y-m-d');
                $date.='-'.rand();
                $relaviteRute.=$date;
                return $this->genereteRute($relaviteRute);
            }
            else
            {

                $rute=$relaviteRute;
                mkdir($rute, 0777, TRUE);
            }
        }
        return $rute;
    }


    /*Carga una imagen
    *@param field Nombre del input
    *@param newName nombre que tendra la imagen
    *@param rute ruta espesifica, si no espesifica se pondra en la carpeta raiz
    *@param same bool false: busca una nueva ruta true: misma ruta espesificada
    */
    public function  uploadImg($field,$newName,$rute, $same)
    {
        $result=null;
        if(isset($_FILES[$field]["name"]))
        {
            $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            if($same)
            {
                $nRute=$rute;
            }
            else
            {
                $nRute=$this->genereteRute($rute);
            }
            $config['upload_path'] = $nRute;
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['overwrite'] = TRUE;
            $config['max_size'] = "2048000";
            $config['file_name'] = $this->renameFileIfExist($newName.".".$ext,$nRute);
            $this->load->library('upload', $config);
            if(!$this->upload->do_upload($field))
            {
                echo $this->upload->display_errors();
            }
            else
            {
                $data = $this->upload->data();
                /*$config['image_library'] = 'gd2';
                $config['source_image'] = $rute.'/'.$data["file_name"];
                $config['create_thumb'] = FALSE;
                $config['maintain_ratio'] = FALSE;
                $config['quality'] = '100%';
                $config['width'] = 200;
                $config['height'] = 200;
                $config['new_image'] = $rute.'/'.$data["file_name"];
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();*/
                $result['full-rute']=base_url().$rute.'/'.$data['file_name'];
            }
        }
        return $result;
    }

        

    //Carga un documento pdf    
    public function uploadFile($field,$newName,$rute,$sameFolder)
    {
        $result=array();
        $date=date('Y-m-d');
        //echo $field.' '.$newName.' '.$rute.''.$sameFolder;

        if(!$sameFolder)
        {
            $rute='public/documents/';
            if (!is_dir($rute.$date)) {
                $rute.=$date;
                mkdir($rute, 0777, TRUE);
            }
            else
            {
                $date.='-'.rand();
                $rute.=$date;
                mkdir($rute, 0777, TRUE);
            }
        }

        if (!is_dir($rute))
        {
            mkdir($rute, 0777, TRUE);
        }

        $config['upload_path'] = $rute;
        $config['allowed_types'] = 'pdf';
        $config['overwrite'] = TRUE;
        $config['max_size'] = "2048000";
        $config['file_name'] = $newName;

        //echo $config['upload_path'];

        //$this->load->library('upload');

        //echo $_FILES[$field]['name'];

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload($field))
        {
            $error = $this->upload->display_errors();

            $result['full-rute']=$error;
            //$this->load->view('upload_form', $error);
        }
        else
        {

            $tipe=$this->upload->data('file_ext');
            $result['rute']=$rute;
            $result['full-rute']=base_url().$rute.'/'.$newName.$tipe;
        }

        return $result;
    }
}
?>