<?php

namespace WOO\STOCK\NOTIFIER\Admin;

// don't call the file directly
defined( 'ABSPATH' ) or die( "Hey! You can't access this file, you silly human!" );

/**
 * The Menu handler class
 */
class Menu {
    /**
     * Initialize the class
     */
    function __construct( ) {
        add_action( 'admin_menu', [ $this, 'wssn_admin_menus' ] );
    }

    /**
     * Register menus
     *
     * @return void
     */
    public function wssn_admin_menus() {
        // Main Menu
        add_menu_page( 
            __( 'Woo Stock Notifier', 'wssn' ), 
            __( 'Woo Stock Notifier', 'wssn' ), 
            'manage_options', 
            'woo-stock-notifier', 
            [ wssn_stock_notifier()->dashboard, 'dashboard_page' ], 
            'dashicons-format-status' 
        );

        //get sub menus array
        $admin_menu     = $this->admin_sub_menu_page();
        
        //admin sub menus
        if( current_user_can( 'manage_options' ) ) {
            foreach ( $admin_menu as $menu_slug => $submenu ) {
                add_submenu_page( 
                        $submenu['parent_slug'], 
                        $submenu['page_title'], 
                        $submenu['menu_title'], 
                        $submenu['capability'], 
                        $menu_slug, 
                        $submenu['callable'], 
                    );
            }
        }
    }

    /**
     * Add sub menu page under main menu
     *
     * @return array
     */
    public function admin_sub_menu_page() {
        $parent_slug = 'woo-stock-notifier';
        $capability  = 'manage_options';
        
        return [
            'woo-stock-notifier' => [
                'parent_slug'   => $parent_slug,
                'page_title'    => __( 'Dashboard', 'wssn' ),
                'menu_title'    => __( 'Dashboard', 'wssn' ),
                'capability'    => $capability,
                'callable'      => [ wssn_stock_notifier()->dashboard, 'dashboard_page' ]
            ],
            'woo-stock-notifier-settings' => [
                'parent_slug'   => $parent_slug,
                'page_title'    => __( 'Settings', 'wssn' ),
                'menu_title'    => __( 'Settings', 'wssn' ),
                'capability'    => $capability,
                'callable'      => [ wssn_stock_notifier()->settings, 'settings_page' ]
            ]
            
        ];
    }
}