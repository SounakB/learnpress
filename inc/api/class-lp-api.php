<?php
class LP_API{
	/**
	 * @var object
	 */
	private static $_instance = false;

    //api endpoint for url
    protected $api_endpoint = 'lp-api/v1';
    //an array that holds string names supported cpts
    static $supported_items = array('courses', 'lessons', 'certified_instructors');


    function __construct(){
        // hook into rest api initialization process, register our route
        add_action( 'rest_api_init', array($this, 'register_lp_course_rest_ep'));
    }

    function register_lp_course_rest_ep(){
        register_rest_route( $this->api_endpoint,'/courses', array(
            'methods' => 'GET',
            'callback' => array($this,'get_REST_courses'),/*
            'args' => array(
              'id' => array(
                'validate_callback' => function($param, $request, $key) {
                  return is_numeric( $param );
                }
              ),
            ),*/
          ) );
          /** this enpoint will not be used at all unless you are running the striderbikes
           * learning suite which includes our own flavor of learnpress and several plugins 
           * for instructor certification
          **/
           register_rest_route($this->api_endpoint, '/certified_instructors', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_REST_certified_instructors'),
          ));
         // if you have the learning path plugin installed you can use this to pull 
         // your paths 
          register_rest_route( $this->api_endpoint,'/learningpaths', array(
            'methods' => 'GET',
            'callback' => array($this,'get_REST_learningpaths'),/*
            'args' => array(
              'id' => array(
                'validate_callback' => function($param, $request, $key) {
                  return is_numeric( $param );
                }
              ),
            ),*/
          ) );
            // gets the curriculum for a specified(id) course
          register_rest_route( $this->api_endpoint,'/courses/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this,'get_REST_course_curriculum'),
            'args' => array(
              'id' => array(
                'validate_callback' => function($param) {
                  return is_numeric( $param );
                }
              ),
            ),
          ) );
    }

    //get the course curriculum
    function get_REST_course_curriculum($data){
      $cID = $data['id'];
      $curr = learn_press_get_course_curriculum($cID);
      return $curr;
    }
    // returns the courses at registered endpoint
    function get_REST_courses () {
        $all_courses = $this->get_posts_by_type();
        return $all_courses;
    }
    // return the learning paths
    function get_REST_learningpaths () {
      $all_courses = $this->get_posts_by_type('lp_learning_path_cpt');
      return $all_courses;
  }

  // returns a list of certified instructors
  function get_REST_certified_instructors(){
    $users = get_users();
    $certified = array();
    foreach($users as $u) {
      $certCourses = $this->get_certification_courses_passed($u->id);
      if(sizeof($certCourses) > 0){
        $certified[$user->display_name] = $certCourses;
      } 
    }
    return $certified;
  }

      /**
     * this is used in by multiple methods
     * @input int user id
     * @out array of courses passed  
     */
    function get_certification_courses_passed($uID){
      $courses = learn_press_get_all_courses();
      $certCourses = array();
      foreach($courses as $c){
          $lp_course = LP_Course::get_course($c);
          $user_grade = $lp_course->evaluate_course_results($uID);
          //echo $user_grade . ' ' . $lp_course->passing_condition . ' ';
          if($user_grade == 100 && get_the_title($c) != 'Brand Enthusiast'){
              $certCourses[] = $c;
          }
      }
      return $certCourses;
  }
    // bit of sql to get all the courses
    function get_posts_by_type($type = 'lp_course') {
		global $wpdb;
		$post_type    =  $type;
		$query        = $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->posts}
			WHERE post_type = %s AND post_status = %s
        ", $post_type, 'publish' );
        $courses = $wpdb->get_results( $query );
        return $courses;
    }
    // get the course by id
    /*
    function get_course_by_id($id = 0) {
      global $wpdb;
      $post_id = $id;
      $query        = $wpdb->prepare( "
        SELECT *
        FROM {$wpdb->posts}
        WHERE ID = %s AND post_status = %s
          ", $post_id, 'publish' );
          $courses = $wpdb->get_results( $query );
          return $courses;
      }
      */
    static function instance() {
		if ( !self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
add_action( 'learn_press_loaded', array( 'LP_API', 'instance' ) );
