<?php
$token = 'pat-';
$baseImageUrl = 'https://';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


// Get the form data from the AJAX request
$receivedData = json_decode(file_get_contents('php://input'), true);
$formData = $receivedData['formData'];



// Log the received data for debugging
file_put_contents('request_log.txt', date('Y-m-d H:i:s') . " - Received data: " . json_encode($formData) . "\n", FILE_APPEND);

// Check if email and phone are both empty
if (empty($formData['email']) && empty($formData['phone'])) {
    http_response_code(200);
    echo json_encode(['error' => 'Email and phone cannot both be empty.']);
    exit();
}

// Map of form IDs to HubSpot file IDs
$formIdToFileIdMap = array(
    '40000008' => '114171701480',
    '30000008' => '114171702481',
    '20000008' => '114142911469',
    '10000008' => '114171702483',
    
    '40000007' => '114171702482',
    '30000007' => '114171702480',
    '20000007' => '114142911480',
    '10000007' => '114142911479',
    
    '40000006' => '114142911979',
    '30000006' => '114142911980',
    '20000006' => '114142911978',
    '10000006' => '114142911982',
    
    '40000002' => '114171703487',
    '30000002' => '114171703488',
    '20000002' => '114171703486',
    '10000002' => '114171703485',
    
    // Add the rest of your form IDs and corresponding HubSpot file IDs
);

// Get the file ID based on the submitted form ID
$fileId = isset($formIdToFileIdMap[$formData['form_id']]) ? $formIdToFileIdMap[$formData['form_id']] : null;



$formIdToImagePathMap = array(
    '40000008' => 'living_and_investing_tr.jpg',
    '30000008' => 'living_and_investing_ua.jpg',
    '20000008' => 'living_and_investing_ru.jpg',
    '10000008' => 'living_and_investing_en.jpg',
    
    '40000007' => 'meet_us_virtually_tr.jpg',
    '30000007' => 'meet_us_virtually_ua.jpg',
    '20000007' => 'meet_us_virtually_ru.jpg',
    '10000007' => 'meet_us_virtually_en.jpg',
    
    '40000006' => 'tips_for_investors_tr.jpg',
    '30000006' => 'tips_for_investors_ua.jpg',
    '20000006' => 'tips_for_investors_ru.jpg',
    '10000006' => 'tips_for_investors_en.jpg',
    
    '40000002' => 'footer_tr.jpg',
    '30000002' => 'footer_ua.jpg',
    '20000002' => 'footer_ru.jpg',
    '10000002' => 'footer_en.jpg',
);

// Get the image URL based on the submitted form ID
$imageUrl = isset($formIdToImagePathMap[$formData['form_id']]) ? $baseImageUrl . $formIdToImagePathMap[$formData['form_id']] : null;
// Prepare the data for the HubSpot API

$contactData  = array(
    'properties' => array(
        'website' => 'desire-antalya.com',
        'lifecyclestage' => 'lead',
        'firstname' => $formData['firstname'],
        'email' => $formData['email'],
        'phone' => $formData['phone'],
        'hs_language' => $formData['hs_language'],
        'utm_source' => $formData['utm_source'],
        'utm_medium' => $formData['utm_medium'],
        'utm_campaign' => $formData['utm_campaign'],
        'utm_keyword' => $formData['utm_keyword'],
        'utm_term' => $formData['utm_term'],
        'location' => $formData['location'],
    )
);

// Function to perform cURL request
function performCurlRequest($url, $data, $token) {
    $jsonData = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ));
    $result = curl_exec($ch);
    if ($result === false) {
        // Handle error
        $error_msg = curl_error($ch);
    }
    curl_close($ch);
    $decodedResult = json_decode($result, true);

    return $decodedResult;
}

// Function to create an engagement (note) in HubSpot
function createEngagement($dealId, $contactId, $imageUrl,$fileId, $token) {
    $engagementData = array(
        'engagement' => array(
            'active' => true,
            'type' => 'NOTE',
            'timestamp' => time() * 1000 // Current time in milliseconds
        ),
        'associations' => array(
            'dealIds' => array($dealId),
            'contactIds' => array($contactId)
        ),
        'metadata' => array(
            'body' => "<p>Form submission image:</p><img src='$imageUrl' alt='Form Image' />"
        ),
        'attachments' => array(
            array(
                'id' => $fileId
            )
        )
    );
    
    $engagementUrl = 'https://api.hubspot.com/engagements/v1/engagements';
    $response = performCurlRequest($engagementUrl, $engagementData, $token);
    return $response;
}


$contactCreationUrl = 'https://api.hubspot.com/crm/v3/objects/contacts';
$contactCreationResponse = performCurlRequest($contactCreationUrl, $contactData, $token);

// Initialize variable for contact ID
$contactId = null;

// Check if contact was successfully created or if it already exists
if (isset($contactCreationResponse['id'])) {
    // Contact was successfully created
    $contactId = $contactCreationResponse['id'];
} elseif (isset($contactCreationResponse['category']) && $contactCreationResponse['category'] == 'CONFLICT') {
    // Contact already exists, extract the existing ID from the message
    // Assuming the error message format is consistent and contains the existing ID
    if (preg_match('/Existing ID: (\d+)/', $contactCreationResponse['message'], $matches)) {
        $contactId = $matches[1];
    }
}



if ($contactId) {
    $b2cPipelineId = '330883560';
    $newDealStageId = '522672623';
     // Prepare the data for the HubSpot API - Deal, including the association
    $dealData = array(
        'associations' => array(
            array(
                'types' => array(
                    array(
                        'associationCategory' => 'HUBSPOT_DEFINED',
                        'associationTypeId' => '3'
                    )
                ),
                'to' => array(
                    'id' => $contactId
                )
            )
        ),
        'properties' => array(
            'dealname' => $formData['firstname'],
            'comment' => $formData['comment'],
            'lead_source' => 'site - desire-antalya.com',
            'dealstage' => $newDealStageId,
            'pipeline' => $b2cPipelineId,
            'utm_source' => $formData['utm_source'],
            'utm_medium' => $formData['utm_medium'],
            'utm_campaign' => $formData['utm_campaign'],
            'utm_keyword' => $formData['utm_keyword'],
            'utm_term' => $formData['utm_term'],
            'form_id' => $formData['form_id'],
            'location' => $formData['location'],
        )
    );

    // Perform the Deal Creation Request
    $dealCreationUrl = 'https://api.hubspot.com/crm/v3/objects/deals';
    $dealCreationResponse = performCurlRequest($dealCreationUrl, $dealData, $token);
    
    // Check if the deal was successfully created
    if (isset($dealCreationResponse['id'])) {
        $dealId = $dealCreationResponse['id'];

        // If an image URL is available for the form, create an engagement
        if ($imageUrl  && fileId) {
            createEngagement($dealId, $contactId, $imageUrl, $fileId, $token);
        }else {
            // Handle the case where imageUrl or fileId is not available
            file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " - Missing imageUrl or fileId for form ID: " . $formData['form_id'] . "\n", FILE_APPEND);
        }
    }
}
?>
