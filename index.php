<?php
// Create and configure Slim app

require './../vendor/autoload.php';
require  './settings/connection.php';

$app = new \Slim\App();
$config = ['settings' => [
    'addContentLengthHeader' => false,
]];
$app = new \Slim\App($config);

// Define app routes
$app->post('/login', function ($request, $response, $args) {

	$input = json_decode($request->getBody());
  $sql = "SELECT * FROM users WHERE email= :email";
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("email", $input->email);
	$stmt->execute();
	$user = $stmt->fetchAll(PDO::FETCH_OBJ);
    // verify email address.
    if(!$user) {
        return $response->withJson(['error' => true, 'message' => 'User does not exist on this email']);
    }
        // verify password.
    if ($input->password != $user[0]->password) {
        return $response->withJson(['error' => true, 'message' => 'Password does not match.']);
    } else {
        return $response->withJson(['error' => false, 'message' => 'login successfuly. Welcome!', 'token' => getAuthToken()]);
    }

});


$app->post('/register', function ($request, $response, $args) {
    $user_name = $request->getParams('user_name');
    $email = $request->getParams('email');
    $password = $request->getParams('password');
    $sql = "INSERT INTO users (user_id, user_name, email, password) VALUES (UUID() ,:name, :email, :password)";
	try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $user_name);
        $stmt->bindParam("email", $email);
        $stmt->bindParam("password", $password);
        $stmt->execute();
		return $response->withJson(['error' => false, 'message' => 'User Created Successfuly','data'=>$user->email]);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
});


$app->group('/product', function () use ($app) {
	$app->get('/get', function ($request, $response, $args) {
		$sql = "select * FROM products";
		try {
			$stmt = getConnection()->query($sql);
			$products = $stmt->fetchAll(PDO::FETCH_OBJ);
			$db = null;
		  return $response->getBody()->write(json_encode($products));
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	});

$app->post('/add', function ($request, $response, $args) {
	$product = json_decode($request->getBody());
    $sql = "INSERT INTO products (product_id, product_name, product_code, description) VALUES (UUID() ,:name, :code, :desc)";
	try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $product->product_name);
        $stmt->bindParam("code", $product->product_code);
        $stmt->bindParam("desc", $product->description);
        $stmt->execute();
		echo json_encode($product);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

});

$app->put('/update/{id}', function ($request, $response, $args) {
	$product = json_decode($request->getBody());
	$sql = "UPDATE products SET product_name=:name, product_code=:code, description=:desc WHERE product_id=:id";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $product->product_name);
        $stmt->bindParam("code", $product->product_code);
        $stmt->bindParam("desc", $product->description);
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        $db = null;
        echo json_encode($product);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

});
$app->delete('/delete/{id}', function ($request, $response, $args) {

    $sql = "DELETE FROM products WHERE product_id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $args['id']);
        $stmt->execute();
        $db = null;
		echo '{"message":{"text":"Record deleted successfully"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
});
})->add(function ($request, $response, $next) {
    $headers = $request->getHeader('Token');
     if($headers[0] != getAuthToken()){
      return $response->withJson(['error' => true, 'message' => 'Unauthorized']);
     };
    return $next($request, $response);
});

// Run app
$app->run();
