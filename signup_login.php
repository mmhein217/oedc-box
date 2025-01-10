<?php
// Replace with your Audiobookshelf API endpoint and API key
define('API_BASE_URL', 'http://192.168.0.253:804');
define('API_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiIyZmZkMDI2ZC02MDk3LTRmYjMtODQzNS01MmYxNjNmNWZhMmEiLCJ1c2VybmFtZSI6InJvb3QiLCJpYXQiOjE3MzQ4MDc4MTF9.U9CJYkvqdiHcZkS8FEChB0IEJaYdTTOy6121Mga86-U');

session_start();
ob_start(); // Start output buffering


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''; // "signup" or "login"
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $type = $_POST['type'] ?? 'user'; // Default to "user" type

    if (empty($username) || empty($password) || empty($type)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $apiUrl = '';
    $payload = [
        'username' => $username,
        'password' => $password,
        'type' => $type
    ];

    if ($action === 'signup') {
        $apiUrl = API_BASE_URL . '/api/users';  // Adjust to match your API signup endpoint
    } elseif ($action === 'login') {
        $apiUrl = API_BASE_URL . '/login';  // Adjust to match your API login endpoint
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
    }

    // Execute API request
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . API_KEY
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 && $httpCode !== 201) {
        echo json_encode(['status' => 'error', 'message' => 'API Error: ' . $response]);
        exit;
    }

    $data = json_decode($response, true);

    if ($action === 'signup') {
        // After user creation, enable the account by setting 'isActive' to true
        $userId = $data['user']['id'] ?? null;  // Get user ID from the response

        if ($userId) {
            // Prepare payload to update the user's account status
            $updatePayload = ['isActive' => true];
            $updateUrl = API_BASE_URL . '/api/users/' . $userId;  // Adjust to match your API user update endpoint

            // Update user status to 'active'
            $ch = curl_init($updateUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');  // Use PATCH to update
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updatePayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . API_KEY
            ]);

            $updateResponse = curl_exec($ch);
            $updateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($updateHttpCode !== 200) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to enable user account.']);
                exit;
            }
        }

        // Return a success message with JS to trigger alert and redirect
        echo "
        <script>
            alert('Signup successful! Your account is now enabled.');
            window.location.href = 'index.html'; // Redirect to the login page after successful signup
        </script>";
        exit;
    }

    if ($action === 'login') {
        // Assuming the token is inside the 'user' array
        $userToken = $data['user']['token'] ?? null;  // Get token from the 'user' array

        if (!$userToken) {
            echo "Response: " . $response; // Debug the raw response
            echo "HTTP Code: " . $httpCode; // Debug the status code
            echo json_encode(['status' => 'error', 'message' => 'Login failed. Token not received.']);
            exit;
        }

        // Fetch user libraries after getting the token
        $ch = curl_init(API_BASE_URL . '/api/libraries');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $userToken
        ]);

        $librariesResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch libraries.']);
            exit;
        }

        $librariesData = json_decode($librariesResponse, true);
        $libraries = $librariesData['libraries'] ?? [];

        if (empty($libraries)) {
            echo json_encode(['status' => 'error', 'message' => 'No libraries accessible.']);
            exit;
        }

        // Redirect to the first library
        //$firstLibraryId = $libraries[0]['id'];
        // Avoid encoding issues in the URL
        //$redirectUrl = 'http://192.168.0.253:804/library/' . $firstLibraryId . '?authToken=' . urlencode($userToken);
        // Perform the actual redirect
        //header('Location: ' . $redirectUrl);
        //exit;

    

        ob_end_clean(); // Clear any output before sending headers
        $output_buffer = ob_get_contents();
        if (!empty($output_buffer)) {
            // Handle the case where output has been buffered
            echo "Output buffer contains: " . $output_buffer; 
            ob_end_clean(); // Clear the buffer
            // Handle the error or provide an alternative response
        } else { 
            // Proceed with the redirection
            $firstLibraryId = $libraries[0]['id']; 
            $userToken = rawurlencode($userToken); 
            $redirectUrl = 'http://192.168.0.253:804/library/' . urlencode($firstLibraryId) . '?authToken=' . $userToken; 
            header('Location: ' . $redirectUrl); 
            exit; 
        }

    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
