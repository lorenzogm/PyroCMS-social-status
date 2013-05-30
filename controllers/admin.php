<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Admin_Controller
{
    protected $section = 'social_status';
    public $namespace = 'social_status';
    public $stream = 'social_status';

    public function __construct()
    {
        parent::__construct();
        $this->load->driver('streams');
    }

    public function index()
    {
        $extra['title'] = lang($this->namespace.':label:social_status');

        if(group_has_role('social_status', 'admin'))
        {
            $extra['buttons'] = array(
                array(
                    'label'     => lang('global:edit'),
                    'url'       => 'admin/'.$this->namespace.'/edit/-entry_id-'
                    ),
                array(
                    'label'     => lang('global:delete'),
                    'url'       => 'admin/'.$this->namespace.'/delete/-entry_id-',
                    'confirm'   => true
                    )
                );
        }

        $this->streams->cp->entries_table($this->stream, $this->namespace, null, null, true, $extra);
    }

    public function create()
    {
        $extra = array(
            'return'            => 'admin/'.$this->namespace,
            'success_message'   => 'Se ha creado correctamente.',
            'failure_message'   => 'Hubo un error.',
            'title'             => 'Crear',
            );
        $this->streams->cp->entry_form($this->stream, $this->namespace, 'new', null, true, $extra, array('status'));
    }

    public function edit($entry_id = null)
    {
        role_or_die('social_status', 'admin');

        $extra = array(
            'return'            => 'admin/'.$this->namespace.'/'.$this->stream.'/',
            'success_message'   => 'Se ha editado correctamente.',
            'failure_message'   => 'Hubo un error.',
            'title'             => 'Editar',
            );
        $this->streams->cp->entry_form($this->stream, $this->namespace, 'edit', $entry_id, true, $extra);
    }

    public function delete($entry_id = 0)
    {
        $this->streams->entries->delete_entry($entry_id, $this->stream, $this->namespace);
        $this->session->set_flashdata('error', 'Se ha borrado con éxito');
        redirect('admin/'.$this->namespace.'/clans');
    }
}