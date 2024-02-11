<?php
/* Template Name: Courses */

get_header();

// Define the URL
$url = 'https://service.fetchcourses.ie/service/fetchcourse.svc/json/SearchCourseListSummaryAdvanced/1/10';

// Define the headers
$headers = [
    'Content-Type: application/json',
    'Cookie: ASP.NET_SessionId=1ug15masbnmn1rd4xxzocvse'
];

// Define the payload
$data = [
    "ISCEDIds" => 0,
    "ProviderIds" => 13,
    "Keywords" => "",
    "LocationIds" => 0,
    "DeliveryModeId" => 0
];

// Initialize cURL session
$curl = curl_init();

// Set the cURL options
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request
$response = curl_exec($curl);
// Check for errors
if ($response === false) {
    echo 'Error: ' . curl_error($curl);
} else {
    // Parse JSON response
    $data = json_decode($response, true);

    // Extract the relevant information (assuming 'courses' contains the list of courses)
    $courses = $data['SearchCourseListSummaryAdvancedResult']['courses'] ?? [];
    // echo '<pre>';
    // var_dump($courses);
    // echo '</pre>';
?>
    <div class="row">


        <?php foreach ($courses as $course) { ?>
            <div class="col-sm-4 col-md-4 col-lg-4">
                <div class="card">
                    <div class="image">
                        <img src="http://loremflickr.com/320/150?random=4" />
                    </div>
                    <a href="<?php echo 'https://www.fetchcourses.ie/course/finder?sfcw-courseId=' . $course['CourseId']; ?>">
                        <div class="card-inner">
                            <div class="header">
                                <h2><?php echo $course['CourseTitle']; ?></h2>
                                <h3><?php echo $course['CourseLocation']; ?></h2>
                            </div>
                            <div class="content">
                                <p>Content area</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
<?php }

// Close cURL session
curl_close($curl);


?>

<?php
get_footer();
