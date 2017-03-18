<?php
/*
	grunt.concat_in_order.declare('Remp_cron');
	grunt.concat_in_order.require('init');
*/


class Remp_cron {
	
	protected $request;
	
	function __construct( $request, $hook ){

		$this->request = $request;
		$recurrence = $request['cron_schedule'];
		
        // delete the task, if the recurrence doesn't match or recurrence doesn't exist
        if ( $recurrence !== wp_get_schedule( $hook ) || ! array_key_exists( $recurrence, wp_get_schedules() ) ) {
            wp_clear_scheduled_hook( $hook );
        }
        
        // add task, if not scheduled and recurrence exists
		if ( ! wp_next_scheduled( $hook ) && array_key_exists( $recurrence, wp_get_schedules() ) ) {
			wp_schedule_event( time(), $recurrence, $hook );
		}

		// task callback
		add_action( $hook, array( $this, 'task_function') );
	
	}
	
	
	public function task_function() {
	  remp_request( $this->request );
	}

}



function remp_cron_init(){
	$requests = remp_get_option( 'request', array() );
	
	// add cron tasks
	$hooks = array();
	foreach ( $requests as $request ){
		if ( $request['state'] === 'cron' ){
			$hook = 'remp_' . $request['id'];
			$hooks[] = $hook;
			new Remp_cron( $request, $hook );
		}
	}
	
	// clear unused cron tasks
	remp_cron_clear( $hooks );
	
}
add_action( 'init', 'remp_cron_init' );



function remp_cron_clear( $ignore = array() ){

	$crons = _get_cron_array();
	foreach( $crons as $cron ){
		foreach( $cron as $cron_key => $cron_val ){
			if ( strpos( $cron_key, 'remp_' ) === 0 && strpos( $cron_key, 'remp_' ) !== false ){

				if ( ! in_array( $cron_key, $ignore ) ) {
					wp_clear_scheduled_hook( $cron_key );
				}
				
			}
		}
	}

}

?>