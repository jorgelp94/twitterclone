<?php

	function connect()
	{
		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "TwitterBase";

		$connection = new mysqli($servername, $username, $password, $dbname);

		// Check connection
		if ($connection->connect_error)
		{
		    return null;
		}
		else
		{
			return $connection;
		}
	}

	function errors($type)
	{
		$header = "HTTP/1.1 ";

		switch($type)
		{
			case 500:	$header .= "500 Bad connection to Database";
						break;
			case 206:	$header .= "206 Wrong Credentials";
						break;
			case 406:	$header .= "406 User Not Found";
						break;
			case 409:	$header .= "409 Conflict, Username already in use please select another one";
						break;
			case 417:	$header .= "417 No content set in the cookie/session";
						break;
			default:	$header .= "404 Request Not Found";
		}

		header($header);
		return array('message' => 'ERROR', 'code' => $type);
	}

	function login($email)
    {
        $conn = connect();

        if ($conn != null)
        {
        	$sql = "SELECT email, fName, lName, passwrd FROM User WHERE email = '$email'";
			$result = $conn->query($sql);

			if ($result->num_rows > 0)
			{
				while($row = $result->fetch_assoc())
		    	{
		    		$response = array('message' => 'OK', 'fName' => $row['fName'], 'lName' => $row['lName'], 'email' => $row['email'], 'password' => $row['passwrd']);
		    	}

		    	$conn->close();
		    	//echo var_dump($response);
		    	return $response;
			}
			else
			{
				$conn->close();
				return errors(406);
			}
        }
        else
        {
        	$conn->close();
        	return errors(500);
        }
    }

    function register($email, $password, $firstName, $lastName, $username)
    {
    	$conn = connect();

    	if ($conn != null)
    	{

    	$sql = "SELECT email FROM User WHERE email = '$email'";
			$result = $conn->query($sql);


    		if ($result->num_rows > 0)
			{
				$conn->close();
				return errors(409);
			}
			else
			{
	    		$sql = "INSERT INTO User (fName, lName, email, username, passwrd) VALUES ('$firstName', '$lastName', '$email', '$username','$password')";

		    	if (mysqli_query($conn, $sql))
	    		{
				    $response  = array('message' => 'OK');
				}

				$conn->close();
				return $response;
			}

    	}
    	else
    	{
    		$conn->close();
    		return errors(500);
    	}
    }

		function getAllUsers() {
			$conn = connect();

			if ($conn != null) {
				$sql = "SELECT fName, lName, email, username FROM User";
				$result = $conn->query($sql);

				$response = array();
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						array_push($response, array('fName' => $row['fName'], 'lName' => $row['lName'], 'email' => $row['email'], 'username' => $row['username']));
					}
					//array_push($response, array('sessionEmail' => $sessionEmail));

					//echo var_dump($response);

					return $response;
				}
				else {
					$conn->close();
					return $response; // no existen items
				}

			}
			else {
				# Connection to Database was not successful
				$conn->close();
				return errors(500);
			}
		}

		function getUserProfile($email) {
			$conn = connect();

			if ($conn != null) {
				$sql = "SELECT fName, lName, email, username FROM User WHERE email = '$email'";
				//echo var_dump($sql);
				$result = $conn->query($sql);

				$response = array();
				if ($result->num_rows > 0) {
					$row = $result->fetch_assoc();
					$response = array('fName' => $row['fName'], 'lName' => $row['lName'], 'email' => $row['email'], 'username' => $row['username']);

					//echo var_dump($response);
					$conn->close();
					return $response;
				}
				else {
					$conn->close();
					return $response; // no existen items
				}

			}
			else {
				# Connection to Database was not successful
				$conn->close();
				return errors(500);
			}
		}

		function startSession($fName, $lName, $email)
	    {
			// Starting the session
		    session_start();
		    if (! isset($_SESSION['fName']))
		    {
		    	$_SESSION['fName'] = $fName;
		    }
		    if (! isset($_SESSION['lName']))
		    {
		    	$_SESSION['lName'] = $lName;
		    }
		    if (! isset($_SESSION['email']))
		    {
		    	$_SESSION['email'] = $email;
		    }
		    //echo var_dump($_SESSION);
	    }

	    function getSession()
	    {
	    	session_start();
	    	if (isset($_SESSION['fName'])) {
	    		$response = array('message' => 'OK' ,'name' => $_SESSION['fName']);
	    		return $response;
	    	} else {
	    		$response = array('name' => 'user');
	    		return $response;
	    	}
	    }

	    function saveUserTweet($mess, $email)
	    {
	    	$conn = connect();

    		if ($conn != null)
    		{
    			$sql = "INSERT INTO Tweet (message, owner) VALUES ('$mess', '$email')";

		    	if (mysqli_query($conn, $sql))
	    		{
				    $response  = array('message' => 'OK');
				} else {
					$response = array('message' => 'ERROR');
				}

				$conn->close();
				return $response;
    		} 
    		else
	    	{
	    		$conn->close();
	    		return errors(500);
	    	}
	    }

	    function loadUserTweets($email)
	    {
	    	$conn = connect();

	        if ($conn != null)
	        {
	        	$sql = "SELECT email, fName, lName, username  FROM User WHERE email = '$email'";
				$result = $conn->query($sql);

				$data = array();
				$response = array();
				if ($result->num_rows > 0)
				{
					while($row = $result->fetch_assoc())
			    	{
			    		// $response = array('message' => 'OK', 'fName' => $row['fName'], 'lName' => $row['lName'], 'username' => $row['username']);
			    		array_push($data, array('fName' => $row['fName'], 'lName' => $row['lName'], 'username' => $row['username']));

			    	}

			    	$sql = "SELECT message, owner FROM Tweet WHERE owner = '$email'";
			    	$result = $conn->query($sql);

			    	if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                        	array_push($data, array('message' => $row['message']));
                        }
                    }

                    $response = $data;

			    	$conn->close();
			    	//echo var_dump($response);
			    	return $response;
				}
				else
				{
					$conn->close();
					return errors(406);
				}
	        }
	        else
	        {
	        	$conn->close();
	        	return errors(500);
	        }
	    }

	    function editUserInfo($email, $fname, $lname, $username)
	    {
	    	$conn = connect();

        	if ($conn != null){

           
                $sql = "UPDATE User SET fName='$fname', lName='$lname', username='$username' WHERE email='$email'";
                if (mysqli_query($conn, $sql)) {
                    $conn->close();
                    return array("status" => "COMPLETE");
                } 
                else {
                    $conn->close();
                    return errors(409);
                }
            
            
        	}
        	else {
            	$conn->close();
            	return errors(500);
        	}
	    }

?>
