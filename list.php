<?php

    $ADD_BASE_ID        = 'add';
    $DELETE_BASE_ID     = 'delete';
    $EDIT_BASE_ID       = 'edit';
    $BOUGHT_BASE_ID     = 'bought';
    $UNBOUGHT_BASE_ID   = 'unbought';
    $DESCRIPTION_BASE_ID= 'description';

    include 'database_access.php';
    include 'list_draw.php';

    $not_logged_in_redirect =     $server_base . '/index.php';

    # First ensure there is a user view requested.
    if (isset($_GET['user'])) {
        $this_user_name = $_GET['user'];
    } else {
        # Some sneaky monkey is trying to get here without being logged in. Back to square one wise guy.
        header('Location: ' . filter_var($not_logged_in_redirect, FILTER_SANITIZE_URL));
    }
    
    # Handle the database interface.
    $db_handle = dbConnect();
    # Check the user is valid.
    $this_user_id = dbValidateUser($db_handle, $this_user_name);

    # Handle All POST requests.
    # Handle adds
    if (isset($_POST[$ADD_BASE_ID])) {
        $item_description = $_POST[$ADD_BASE_ID];
        if ($item_description != null and !empty($item_description)) {
            #echo 'To Add: "' . $item_description . '"';
            dbAddItem($db_handle, $this_user_id, $item_description);
        }
    }
    # Handle deletes
    if (isset($_POST[$DELETE_BASE_ID])) {
        foreach ($_POST[$DELETE_BASE_ID] as $item_id=>$item_data) {
            #echo 'To Delete: "' . $item_id . '" : "' . $item_data . '"';
            dbDeleteItem($db_handle, $item_id);
        }
    }
    # Handle edits
    if (isset($_POST[$EDIT_BASE_ID])) {
        foreach ($_POST[$EDIT_BASE_ID] as $item_id=>$item_data) {
            #echo 'To Edit: "' . $item_id . '" : "' . $item_data . '"';
            dbEditItem($db_handle, $item_id, $item_data);
        }
    }
    # Handle boughts
    if (isset($_POST[$BOUGHT_BASE_ID])) {
        foreach ($_POST[$BOUGHT_BASE_ID] as $item_id=>$item_data) {
            #echo 'To Bought: "' . $item_id . '" : "' . $item_data . '"';
            dbMarkBought($db_handle, $this_user_id, $item_id);
        }
    }
    # Handle unboughts
    if (isset($_POST[$UNBOUGHT_BASE_ID])) {
        foreach ($_POST[$UNBOUGHT_BASE_ID] as $item_id=>$item_data) {
            #echo 'To Unbought: "' . $item_id . '" : "' . $item_data . '"';
            dbMarkUnbought($db_handle, $item_id);
        }
    }

    # Now the database is up to date, get all list items.
    $users_items = dbGetAllUsersItems($db_handle);
    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Xmas List</title>
    <link href="style.css" rel="stylesheet" type="text/css" />
    <script src="https://use.fontawesome.com/141f2d0518.js"></script>
    <script>
        function editItem(item_id, item_description) {
            var descId   = <?php echo '"' . formId($DESCRIPTION_BASE_ID,    '" + item_id + "') . '"'; ?>;
            var editId   = <?php echo '"' . formId($EDIT_BASE_ID,           '" + item_id + "') . '"'; ?>;
            var buttonId = <?php echo '"' . formId($EDIT_BASE_ID.'_button', '" + item_id + "') . '"'; ?>;
            var oldDesc = document.getElementById(descId);

            if (oldDesc) {
                // Not in edit mode - create new edit field.
                var newDesc = document.createElement("input");
                newDesc.type = "text";
                newDesc.className = "listeditor";
                newDesc.id =   editId;
                newDesc.name = editId;
                newDesc.value = item_description;
                oldDesc.parentNode.replaceChild(newDesc, oldDesc);
                // Update edit button.
                var button = document.getElementById(buttonId);
                button.innerHTML = '<i class="fa fa-arrow-right fa-fw"></i>';
            } else {
                // Already in edit mode, just submit the form as requested.
                document.getElementById("list_form").submit();
            }
        }

        function addItem() {
            var addId    = <?php echo '"' . $ADD_BASE_ID . '"'; ?>;
            var buttonId = <?php echo '"' . $ADD_BASE_ID . '_button"'; ?>;
            var field = document.getElementById(addId);

            if (field) {
                // Already in add mode, just submit the form as requested.
                document.getElementById("list_form").submit();
            } else {
                // Not in add mode - create add field.
                var button = document.getElementById(buttonId);
                field = document.createElement("input");
                field.type = "text";
                field.className = "listeditor";
                field.id   = addId;
                field.name = addId;
                button.parentNode.insertBefore(field, button);
                // Update add button.
                button.innerHTML = '<i class="fa fa-arrow-right fa-fw"></i>';
            }
        }
    </script>
</head>

<body>
<div class="body">
    <h1 class="banner">Simmonds Xmas List 2016</h1>

    <?php
        echo '<form id="list_form" method="POST" action="list.php?user='.$this_user_name.'" id="listForm">';

        # Add a hidden default button. This prevents accidentally performing
        # random actions when pressing CR on some browsers which interpret CR as
        # a submission of the first element.
        echo '<button id="form_default" type="submit" value="default action"></button>';
        
        foreach ($users_items as $user) {
            $is_this_user = (strcmp($user['name'], $this_user_name) == 0);
            
            echo '<h2>'.$user['name'].'</h2>';
            echo '<ul>';
            foreach ($user['items'] as $item) {
                $item_id = $item['item_id'];
                $is_bought = (strcmp($item['bought'], "1") == 0);
                $buyer_is_this_user = (((int) $item['buyer_id']) == $this_user_id);
                echo '<li>';
                echo drawDescription($item_id, $item['description'], $is_this_user, $is_bought);
                # Print the controls.
                if ($is_this_user) {
                    # If this is the current user - print edit and delete.
                    echo drawEditButton($item_id, $item['description']);
                    echo drawDeleteButton($item_id);
                } else {
                    # Otherwise add the bought button if unbought, or un-buy if
                    # bought and bought by the current user.
                    if (!$is_bought) {
                        echo drawBoughtButton($item_id);
                    } else if ($buyer_is_this_user) {
                        echo drawUnboughtButton($item_id);
                    }
                }
                echo '</li>';
            }
            # If current user, allow them to add to the list.
            if ($is_this_user) {
                echo '<li>';
                echo drawAddButton();
                echo '</li>';
            }
            echo '</ul>';
        }
        echo '</form>';
    ?>
</div>
</body>
</html>
