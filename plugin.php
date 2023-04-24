<?php

defined("ABSPATH") or die();

if( !class_exists("CUSTOM_PLUGIN") ) {
    
    class CUSTOM_PLUGIN {

        const REQUIREMENTS = [
            'Listdom' => 'listdom/listdom.php',
        ];

        public function __construct()
        {
            if($this->check_requiremnts())
                $this->__hooks();
        }

        public function __hooks() {
            add_filter('lsd_before_search' , [$this , 'apply_custom_query']);
        }

        public function apply_custom_query($args) {
            
            if(!empty($args['s'])) {
                
                $args['tax_query'] = [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'listdom-category',
                        'field' => 'term_id',
                        'terms' => $this->search_in_categories($args['s']),
                        'operator' => 'IN'
                    ]
                ];

                $args['s'] = null;
            }
            
            return $args;
        }

        public function search_in_categories($s) {
            
            $args = array(
                'taxonomy'               => 'listdom-category',
                'orderby'                => 'name',
                'order'                  => 'ASC',
                'hide_empty'             => false,
                'search'                 => $s
            );
            $the_query = new WP_Term_Query($args);
            $terms = [];
            foreach($the_query->get_terms() as $term) {
                $terms[] = strval($term->term_id);
            }

            return $terms;

        }

        public function check_requiremnts() {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            foreach(CUSTOM_PLUGIN::REQUIREMENTS as $plugin => $requirement) {
                if(!is_plugin_active($requirement)) {
                    add_action('admin_notices' , function() use($plugin) {

                        ?>

                        <div class="notice notice-error is-dismissible">
                            <p>
                                To run the plugin correctly, you need to install and activate the <strong><?=$plugin;?></strong> plugin
                            </p>
                        </div>

                        <?php

                    });
                    return false;
                }
            }
            return true;
        }

    }

}