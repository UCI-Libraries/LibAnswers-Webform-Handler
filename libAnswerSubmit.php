<?php
// Allows access from a specific origin for cross-origin resource sharing (CORS)
header('Access-Control-Allow-Origin: https://lib.uci.edu');
// Sets the content type of the response to JSON, indicating that the output is JSON-formatted
header('Content-type: application/json');

// Parses an INI file containing the app credentials and stores the result in a variable
$appCredentials = parse_ini_file('/var/www/config/libAnswersConfig.ini');

// Function to retrieve an access token using the app credentials
function getAccessToken() {
    global $appCredentials; // Reference the global variable within the function scope
    try {      
        // Assembles the payload for the request
        $payload = 'client_id='. $appCredentials['client_id'].'&client_secret='. $appCredentials['client_secret'] .'&grant_type=client_credentials';

        // Initializes a new cURL session to the token endpoint
        $ch = curl_init('https://uci.libanswers.com/api/1.1/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Modified to use the payload variable
        curl_setopt($ch, CURLOPT_FAILONERROR, true); 
        
        // Sets the HTTP headers including authorization and content type
        $authorization = base64_encode($appCredentials['client_id'].':'.$appCredentials['client_secret']);
        $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        
        // Executes the cURL session, submitting the POST request, then closes the session
        $result = curl_exec($ch);
        curl_close($ch);
        
        if($result){
            // Decodes the JSON response to retrieve the access token
            $response = json_decode($result);
            error_log('token '. $response->access_token);
            return $response->access_token;
        } else {
            // Handle case where no result is returned
        }
    } catch (Exception $e) {
        error_log('Caught exception: '. $e->getMessage());
    }
}

// Function to import tickets with given token
function importTickets($token) {
    // Assembles the team members information and project details from the POST request
    $teamMembers = 'Leader/PI/First reviewer: '.$_POST['team_members_table_01_leader'].'<br>'
                    .'Co-PI / Second Reviewer: '.$_POST['team_members_table_02_leader'].'<br>'
                    .'Statistical Expert: '.$_POST['team_members_table_03_leader'].'<br>'
                    .'Content Expert: '.$_POST['team_members_table_04_leader'].'<br>'
                    .'Project Organizer: '.$_POST['team_members_table_03_leader'].'<br>';
    // Additional project details appended to the submission info
    // Includes handling optional fields and concatenating values from form inputs
    $submissionInfo = '<b>Team members:</b> <br>'.$teamMembers.'<br>'
                        .'<b>Research question:</b> '.$_POST['what_is_your_research_question'].'<br><br>'
                        .'<b>Type of project:</b> '.$_POST['what_type_of_evidence_synthesis_project_are_you_doing_'].'<br><br>';
                        if(!empty($_POST['other_type'])){
                            $submissionInfo .= '<b>Other type:</b> '. $_POST['other_type'].'<br/>';
                        }
    $submissionInfo .= '<b>UCI affiliation:</b> '.$_POST['uci_affiliation'].'<br><br>';
    $submissionInfo .= '<b>UCI dept. or program:</b> '.$_POST['uci_department_or_program'].'<br><br>';
    $submissionInfo .= '<b>Topic of your project:</b> '.$_POST['please_explain_more_about_the_topic_of_your_project_what_is_the_'].'<br><br>';
    $areasOfStudy = '';
    if(!empty($_POST['please_select_the_area_of_study_that_best_represents_your_projec'])){
        $areasOfStudy = $_POST['please_select_the_area_of_study_that_best_represents_your_projec'];
    }
    $submissionInfo .= '<b>Areas of study:</b> '. $areasOfStudy . '<br>';
    $submissionInfo .= '<b>Citation info:</b> ' . $_POST['please_share_citation_information_doi_or_pmid_is_sufficient_for_'] . '<br>'
                    . '<b>Timeline:</b> ' . $_POST['what_is_your_proposed_project_timeline_'] . '<br>'
                    . '<b>Discussed with librarian:</b> ' . $_POST['have_you_already_discussed_this_project_with_a_librarian_please_'] . '<br>';
                    if(!empty($_POST['other_librarian'])){
                        $submissionInfo .= '<b>Other Librarian:</b> '. $_POST['other_librarian'].'<br/>';
                    }
    $submissionInfo .= '<b>Existing protocol:</b> ' . $_POST['do_you_have_an_existing_protocol_for_this_project_'] . '<br>';
    $submissionInfo .= '<b>Link to protocol:</b> ' . $_POST['please_share_a_link_to_your_protocol_whether_finished_or_still_i'] . '<br>';
    $software = '';
    foreach($_POST['free_from_uci'] as $key => $value){
        $software .= $value . ', ';
    }
    foreach($_POST['require_paid_subscription'] as $key => $value){
        $software .= $value . ', ';
    }
    foreach($_POST['other'] as $key => $value){
        $software .= $value . ', ';
    }
    $submissionInfo .=  '<b>Systematic review software:</b> ' . rtrim($software,", ") . '<br>';
    if(!empty($_POST['other_software'])){
        $submissionInfo .= '<b>Other software:</b> '. $_POST['other_software'].'<br/>';
    }
    $submissionInfo .=  '<b>Bib mgmt software:</b> ' . $_POST['which_bibliographic_management_software_also_known_as_citation_o'] . '<br>';
    // Converts the assembled data into a format suitable for sending via cURL
    
    $postData = "quid=XXXX&pquestion=".$_POST['what_is_your_research_question']."&pname=".$_POST['full_name_of_project_lead']."&pemail=".$_POST['uci_email_address']."&pdetails=".$submissionInfo;
    // Initializes a cURL session to the ticket creation endpoint with appropriate options
    $ch = curl_init('https://uci.libanswers.com/api/1.1/ticket/create');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);   
    curl_setopt($ch, CURLOPT_POST, 1);   
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' .$token,
        'Content-Type: application/x-www-form-urlencoded' 
    ));
    
    // Executes the cURL session to submit the POST request, then closes the session
    $result = curl_exec($ch);
    if(curl_error($ch))
    {
        // Logs any cURL error encountered
        error_log('curl error is -' . curl_error($ch));
    }
    error_log(curl_getinfo( $ch, CURLINFO_RESPONSE_CODE ));
    curl_close($ch);
    
    // Processes the JSON response, generating a response based on ticket creation success/failure
    $responseJson = json_decode($result);
    if($responseJson && !empty($responseJson->ticketUrl)){
        // Handles successful ticket creation
        $ticketId = explode("=",(string)$responseJson->ticketUrl)[1];
        $msg = [
            'success'  => true,
            'msg'      => '',
            'ticketUrl' => $ticketId,
        ];
        return json_encode($msg);
    } else {
        // Handles failed ticket creation
        $msg = [
            'success'  => false,
            'msg'      => '',
            'ticketUrl' => '',
        ];
        return json_encode($msg);
    }
}

// Retrieves an access token and uses it to import tickets
$token = getAccessToken();
importTickets($token);

?>
