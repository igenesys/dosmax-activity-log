<?php
/**
 * AJAX Handler for Dosmax Activity Log Demo
 */

// Simple AJAX handler for demo purposes
if (isset($_POST['action']) && $_POST['action'] === 'dosmax_get_log_details') {
    header('Content-Type: application/json');
    
    $occurrence_id = intval($_POST['occurrence_id'] ?? 0);
    
    // Sample metadata based on your database screenshots
    $sample_data = array(
        1 => array(
            'PostTitle' => 'Seizoensallergieën bij huisdieren: waar moet je op letten?',
            'PostDate' => '2025-06-14 11:07:57',
            'PostUrl' => 'https://hdvs-20.siteshowroom.nl/seizoensallergieen-bij-huisdieren-waar-moet-je-op-letten/',
            'OldTitle' => 'Seizoensallergieën bij huisdieren: waar moet je op...',
            'NewTitle' => 'Seizoensallergieën bij huisdieren: waar moet je op letten?',
            'EditorLinkPost' => 'https://hdvs-20.siteshowroom.nl/wp-admin/post.php?post=321&action=edit',
            'PostID' => '321',
            'PostType' => 'post',
            'PostStatus' => 'publish',
            'UserAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36',
            'SessionID' => '3e75d072bae8-3c458f782e00a8ab9f8b3ebb58969d4f0',
            'ClientIP' => '46.243.189.112'
        ),
        2 => array(
            'PostTitle' => 'Home',
            'PostDate' => '2025-08-14 08:08:15',
            'PostUrl' => 'https://hdvs-20.siteshowroom.nl/',
            'EditorLinkPost' => 'https://hdvs-20.siteshowroom.nl/wp-admin/post.php?post=1&action=edit',
            'PostID' => '1',
            'PostType' => 'page',
            'PostStatus' => 'publish',
            'UserAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36',
            'SessionID' => '3e75d072bae8-3c458f782e00a8ab9f8b3ebb58969d4f0',
            'ClientIP' => '46.243.189.112'
        )
    );
    
    if (!isset($sample_data[$occurrence_id])) {
        echo json_encode(array('success' => false, 'data' => 'Occurrence not found'));
        exit;
    }
    
    $metadata = $sample_data[$occurrence_id];
    
    // Format response data based on occurrence ID
    if ($occurrence_id == 1) {
        $response_data = array(
            'date' => '14.08.2025 8:09:29.000 am',
            'user' => 'jarik',
            'user_roles' => 'site_admin',
            'ip' => $metadata['ClientIP'],
            'event_id' => '2100',
            'severity' => '200',
            'object' => 'post',
            'event_type' => 'opened',
            'message' => 'Opened the product <strong>' . htmlspecialchars($metadata['PostTitle']) . '</strong> in the editor.',
            'metadata' => $metadata
        );
    } else {
        $response_data = array(
            'date' => '14.08.2025 8:08:15.000 am',
            'user' => 'jarik',
            'user_roles' => 'site_admin',
            'ip' => $metadata['ClientIP'],
            'event_id' => '2101',
            'severity' => '200',
            'object' => 'page',
            'event_type' => 'viewed',
            'message' => 'Viewed the page <strong>' . htmlspecialchars($metadata['PostTitle']) . '</strong>.',
            'metadata' => $metadata
        );
    }
    
    echo json_encode(array('success' => true, 'data' => $response_data));
    exit;
}

// Handle other requests
http_response_code(404);
echo json_encode(array('success' => false, 'data' => 'Invalid action'));
?>