<?php

function dbConnect() {
    $db_hostname = 'localhost';
    $db_database = 'xmas_list';
    $db_username = 'root';
    $db_password = 'root';

    # Connect
    ini_set('display_errors', 1);
    $db_handle = new PDO("mysql:host=$db_hostname;dbname=$db_database", $db_username, $db_password);
    $db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $db_handle;
}

function dbDisconnect($db_handle) {
    # Nothing to do in a PDO implementation
}

function dbValidateUser($db_handle, $user_name) {
    # Check if user exists.
    $get_statement = $db_handle->prepare('SELECT user_id FROM users WHERE name = :user_name');
    $get_statement->execute(array(':user_name' => $user_name));
    $user_row = $get_statement->fetch(PDO::FETCH_ASSOC);

    if (isset($user_row) and $user_row != null and count($user_row) > 0) {
        # User exists.
        return (int) $user_row['user_id'];
    } else {
        # User does not exist.
        return -1;
    }
}

function dbGetAllUsersItems($db_handle) {
    $users_statement = $db_handle->prepare("SELECT users.user_id, users.name FROM users ORDER BY users.name ASC");
    $users_statement->execute();
    $users_rows = $users_statement->fetchAll(PDO::FETCH_ASSOC);

    // TODO: Optimise - we only need 1 query, not n+1...
    for ($i = 0; $i < count($users_rows); $i++)
    {
        $items_statement = $db_handle->prepare("SELECT items.item_id, items.description, items.bought, items.buyer_id FROM items WHERE items.requester_id = :user_id ORDER BY items.item_id ASC");
        $items_statement->execute(array(':user_id' => $users_rows[$i]['user_id']));
        $users_rows[$i]['items'] = $items_statement->fetchAll(PDO::FETCH_ASSOC);
    }
    return $users_rows;
}

function dbAddItem($db_handle, $user_id, $item_description) {
    #$item_description = escapeString($item_description);
    $statement = $db_handle->prepare("INSERT INTO items (requester_id, description) VALUES (:user_id, :description)");
    $statement->execute(array(':user_id' => $user_id, ':description' => $item_description));
}

function dbDeleteItem($db_handle, $item_id) {
    $statement = $db_handle->prepare("DELETE FROM items WHERE item_id = :item_id");
    $statement->execute(array(':item_id' => $item_id));
}

function dbEditItem($db_handle, $item_id, $item_description) {
    #$item_description = escapeString($item_description);
    $statement = $db_handle->prepare("UPDATE items SET description = :description WHERE item_id = :item_id");
    $statement->execute(array(':item_id' => $item_id, ':description' => $item_description));
}

function dbMarkBought($db_handle, $user_id, $item_id) {
    $statement = $db_handle->prepare("UPDATE items SET bought = 1, buyer_id = :buyer_id WHERE item_id = :item_id");
    $statement->execute(array(':item_id' => $item_id, ':buyer_id' => $user_id));
}

function dbMarkUnbought($db_handle, $item_id) {
    $statement = $db_handle->prepare("UPDATE items SET bought = 0, buyer_id = NULL WHERE item_id = :item_id");
    $statement->execute(array(':item_id' => $item_id));
}

?>
