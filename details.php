<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Social_status extends Module
{
    public $version = '1.0';
    public $namespace = 'social_status';

    public function info()
    {
        $this->load->language('social_status/social_status');

        $info = array(
            'name' => array(
                'en' => 'Social status',
                ),
            'description' => array(
                'en' => 'Social status.',
                ),
            'frontend' => false,
            'backend' => true,
            'menu' => 'content',
            'roles'    => array(
                'admin'
            ),
            'sections' => array(
                'social_status' => array(
                    'name' => $this->namespace.':label:social_status',
                    'uri' => 'admin/'.$this->namespace.'',
                    'shortcuts' => array(
                        array(
                            'name' => $this->namespace.':shortcuts:create',
                            'uri' => 'admin/'.$this->namespace.'/create',
                            'class' => 'add'
                            ),
                        ),
                    ),
                ),
            );

return $info;
}

public function install()
{
    /* custom_data
    -------------------------------------------------- */
    $streams = array('social_status');

    $fields_assignment = array(
        'social_status' => array('facebook_message', 'twitter_message', 'url')
        );

    $streams_options = array(
        'social_status' => array(
            'view_options' => array('facebook_message', 'twitter_message', 'url'),
            'title_column' => 'facebook_message'
            ),
        );

    /* dependencies
    -------------------------------------------------- */
    $this->load->driver('streams');
    $this->load->language('social_status/social_status');

    /* uninstall
    -------------------------------------------------- */
    if( ! $this->uninstall())
        return false;

    /* streams
    -------------------------------------------------- */
    $streams_id = $this->add_streams($streams, $streams_options);

    /* fields
    -------------------------------------------------- */
    $fields = array();

    // social_status
    $fields['facebook_message'] = array('name' => $this->lang('facebook_message'), 'slug' => 'facebook_message', 'type' => 'textarea', 'instructions' => $this->lang('facebook_message', 'instructions'));
    $fields['twitter_message'] = array('name' => $this->lang('twitter_message'), 'slug' => 'twitter_message', 'type' => 'text', 'extra' => array('max_length' => 140), 'instructions' => $this->lang('twitter_message', 'instructions'));
    $fields['url'] = array('name' => $this->lang('url'), 'slug' => 'url', 'type' => 'url', 'required' => false, 'instructions' => $this->lang('url', 'instructions'));

    $this->add_fields($fields);

    /* fields_assignment
    -------------------------------------------------- */
    $this->add_fields_assignment($streams, $fields, $fields_assignment);

    return true;
}

public function uninstall()
{
    $this->load->driver('streams');

    $this->streams->utilities->remove_namespace($this->namespace);

    return true;
}

public function upgrade($old_version)
{
        // Your Upgrade Logic
    return true;
}

public function help()
{
        // Return a string containing help info
        // You could include a file and return it here.
    return "No documentation has been added for this module.<br />Contact the module developer for assistance.";
}

public function add_streams($streams, $streams_options)
{
    $streams_id = array();
    foreach ($streams as $stream)
    {
        if ( ! $this->streams->streams->add_stream($this->lang($stream), $stream, $this->namespace, $this->namespace.'_', null)) return false;
        else
            $streams_id[$stream] = $this->streams->streams->get_stream($stream, $this->namespace)->id;

        $this->update_stream_options($stream, $streams_options[$stream]);
    }

    return $streams_id;
}

public function update_stream_options($stream, $stream_options)
{
        // Update about, title_column and view options
    $update_data = array(
        'about'        => 'lang:'.$this->namespace.':'.$stream.':about',
        'view_options' => $stream_options['view_options'],
        'title_column' => $stream_options['title_column']
        );
    $this->streams->streams->update_stream($stream, $this->namespace, $update_data);
}

public function build_template($stream = null)
{
    if($stream)
        return array('title_column' => FALSE, 'required' => TRUE, 'unique' => FALSE);
    else
        return array('namespace' => $this->namespace, 'type' => 'text');
}

public function create_folders($array)
{
    $this->load->library('files/files');
    $this->load->model('files/file_folders_m');

    $folder = Files::search($this->namespace);
    if( ! $folder['status'])
        Files::create_folder($parent = '0', $folder_name = $this->namespace);
    $folders[$this->namespace] = $this->file_folders_m->get_by('name', $this->namespace);

    foreach ($array as $label)
    {
        $folder = Files::search($label);
        if( ! $folder['status'])
            Files::create_folder($parent = $folders[$this->namespace]->id, $folder_name = $label);
        $folders[$label] = $this->file_folders_m->get_by('name', $label);
    }

    return $folders;
}

public function add_fields($fields)
{
    foreach($fields AS &$field)
        $field = array_merge($this->build_template(), $field);
    $this->streams->fields->add_fields($fields);
}

public function add_fields_assignment($streams, $fields, $fields_assignment)
{

    foreach ($streams as $stream)
    {
        $assign_data = array();
        foreach($fields_assignment[$stream] as $field_assignment)
            $assign_data[] = array_merge($this->build_template($stream), $fields[$field_assignment]);

        foreach($assign_data as $assign_data_row)
        {
            $field_slug = $assign_data_row['slug'];
            unset($assign_data_row['name']);
            unset($assign_data_row['slug']);
            unset($assign_data_row['type']);
            unset($assign_data_row['extra']);
            $this->streams->fields->assign_field($this->namespace, $stream, $field_slug, $assign_data_row);
        }
    }

}

public function build_choice_field($array, $label, $choice_type, $default_value = 0)
{
    $flag = true;
    $string = '';
    foreach ($array AS $key)
    {
        if($flag)
            $flag = false;
        else
            $string .= "\n";

        $string .= "$key : ".$this->lang($key);
    }

    return array('name' => 'lang:'.$this->namespace.':label:'.$label, 'slug' => $label, 'type' => 'choice', 'extra' => array('choice_data' => $string, 'choice_type' => $choice_type, 'default_value' => $default_value));
}

public function lang($label, $type = 'label')
{
    return 'lang:'.$this->namespace.':'.$type.':'.$label;
}

}