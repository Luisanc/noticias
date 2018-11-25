<?php

class Admin extends M_controller
{

    public function index()
    {
        $this->load->model('AdminModel');
        if(isset($_SESSION['Userid']))
        {
            $this->data['nombre']=$_SESSION['UserName'];
            //Login exitoso
            //echo 'Bienvenido '.$this->data['nombre'];
            $this->noticias();
        }
        else
        {
            redirect(base_url().'login');
        }
    }

    public function bLogin()
    {
        $this->load->library('form_validation');
        $this->load->model('AdminModel');
        $this->load->helper(array('form', 'url'));

        $this->form_validation->set_rules('email', 'usuario', 'required');
        $this->form_validation->set_rules('password', 'contraseña', 'required');

        if ($this->form_validation->run() === FALSE)
        {            
            $this->data['title']="Login";            
            $this->loadView('public/login',$this->data);
        }
        else
        {
            $username = $this->input->post('email');
            $password = $this->input->post('password');
            $resultado=$this->Login($username,$password);

            //var_dump($resultado);            
            if(!empty($resultado))
            {

                $this->data['nombre']=$_SESSION['UserName'];
                $this->data['title']='ADMIN';                
                redirect(base_url().'administrador/ver/noticias/0',$this->data);                
            }
            else
            {
                redirect(base_url().'login');
            }
        }
    }

    public function bLogout()
    {
        $this->Logout();
        redirect(base_url());
    }
    
    public function noticias($pages=0)
    {        
        if(isset($_SESSION['UserName']))
        {
            $this->load->model('AdminModel');
            $this->data['title']='ADMIN';
            $this->data['nombre']=$_SESSION['UserName'];
            $this->data['noticias']=$this->AdminModel->getNoticias();        
            $this->loadViewAdmin('admin-view-news',$this->data);
        }
        else
        {            
            redirect(base_url()."login");
        }        
    }

    public function addNoticia()
    {
        if(isset($_SESSION['Userid']))
        {
            $this->load->helper(array('form', 'url'));
            $this->load->library('form_validation');
            $this->load->model('AdminModel');

            $this->form_validation->set_rules('nombre', 'nombre', 'required');
            $this->form_validation->set_rules('descripcion', 'descriction', 'required');
            $this->form_validation->set_rules('contenido', 'contenido', 'required');

            if ($this->form_validation->run() === FALSE)
            {
                $this->data['title']='noticias';
                $this->data['nombre']=$_SESSION['UserName'];            
                $this->loadViewAdmin('admin-add-news',$this->data);
            }
            else
            {
                $idP=null;
                $isInsert=true;
                if($this->input->post('idNoticia')!=null)
                {
                    $isInsert=false;
                    $idP=$this->input->post('idNoticia');
                }
                $cover=$this->uploadImg('imagen','cover','',false);
                $date=new DateTime(); //this returns the current date time
                $result = $date->format('Y-m-d-H-i-s');
                $nPlant=array(
                    'id'=>$idP,
                    'fecha'=>$result,
                    'nombre'=>$this->input->post('nombre'),
                    'descripcion'=>$this->input->post('descripcion'),
                    'contenido'=>$this->input->post('contenido'),
                    'imagen'=>!empty($cover['full-rute'])?$cover['full-rute']:''
                );

                if($isInsert)
                {
                    $this->AdminModel->addNoticia($nPlant);
                    redirect(base_url().'administrador/ver/noticias/0');
                }
                else
                {
                    $this->AdminModel->updateNoticia($nPlant);
                    redirect(base_url().'administrador/ver/noticias/0');
                }
            }
        }
        else
        {    
            redirect(base_url().'login');
        }        
    }
    
    public function editNoticia($id)
    {
        if(isset($_SESSION['Userid']))
        {
            $this->data['title']='EDITAR NOTICIA';
            $this->data['nombre']=$_SESSION['UserName'];
            $this->load->model('AdminModel');
            $this->data['noticias']=$this->AdminModel->getNoticia($id);                
            $this->loadViewAdmin('admin-add-news',$this->data);
        }
        else
        {
            redirect(base_url().'login');
        }        
    }

    public function eliminarPlanta($id)
    {
        if(isset($_SESSION['Userid']))
        {

        }
        else
        {
            redirect(base_url().'login');            
        }
    }

    public function eliminarNoticia($id)
    {
        if(isset($_SESSION['Userid']))
        {
            
        }
        else
        {
            redirect(base_url().'login');
        }
    }

}