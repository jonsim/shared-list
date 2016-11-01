<?php

function formId($base, $id) {
    return $base . '[' . $id . ']';
}

function autoLink($text) {
    # Automatically extract all URLs and replace them with links.
    $regex = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
    return preg_replace($regex, '<a href="$0">$0</a>', $text);
}

function drawDescription($item_id, $item_description, $is_this_user, $is_bought) {
    global $DESCRIPTION_BASE_ID;
    # When *displaying* the data, escape html special characters to prevent XSS.
    # All quotes can (and should) be left as is since we are just splatting them
    # verbatim onto the page.
    $item_description = htmlspecialchars($item_description);
    # Print the item description (optionally crossed through).
    $strike = (!$is_this_user && $is_bought);
    $output  = '<span class="listcontent" ';
    $output .=   'id="'.formId($DESCRIPTION_BASE_ID, $item_id).'">';
    if ($strike) {
        $output .= '<strike>';
    };
    $output .= autoLink($item_description);
    if ($strike) {
        $output .= '</strike>';
    }
    $output .= '</span>';
    return $output;
}

function drawEditButton($item_id, $item_description) {
    global $EDIT_BASE_ID;
    # When *inputting* the data into the edit box, we still have to escape html
    # special characters (this time to prevent " etc escaping their bounds), but
    # also must escape slashes (to prevent ' escaping their bounds - merely
    # converting these to escaped html is no good since this will be rendered on
    # the page in an onclick)!
    $item_description = addslashes(htmlspecialchars($item_description));
    $output  = '<button type="button" ';
    $output .=     'id="'.formId($EDIT_BASE_ID . '_button', $item_id).'" ';
    $output .=   'name="'.formId($EDIT_BASE_ID . '_button', $item_id).'" ';
    $output .=   'onclick="editItem('.$item_id.', \''.$item_description.'\')">';
    $output .= '<i class="fa fa-pencil fa-fw"></i>';
    $output .= '</button>';
    return $output;
}

function drawDeleteButton($item_id) {
    global $DELETE_BASE_ID;
    $output  = '<button type="submit" ';
    $output .=     'id="'.formId($DELETE_BASE_ID, $item_id).'" ';
    $output .=   'name="'.formId($DELETE_BASE_ID, $item_id).'">';
    $output .= '<i class="fa fa-trash fa-fw"></i>';
    $output .= '</button>';
    return $output;
}

function drawBoughtButton($item_id) {
    global $BOUGHT_BASE_ID;
    $output  = '<button type="submit" ';
    $output .=     'id="'.formId($BOUGHT_BASE_ID, $item_id).'" ';
    $output .=   'name="'.formId($BOUGHT_BASE_ID, $item_id).'">';
    $output .= '<i class="fa fa-shopping-cart fa-fw"></i>';
    $output .= '</button>';
    return $output;
}

function drawUnboughtButton($item_id) {
    global $UNBOUGHT_BASE_ID;
    $output  = '<button type="submit" ';
    $output .=     'id="'.formId($UNBOUGHT_BASE_ID, $item_id).'" ';
    $output .=   'name="'.formId($UNBOUGHT_BASE_ID, $item_id).'">';
    $output .= '<i class="fa fa-unlock fa-fw"></i>';
    $output .= '</button>';
    return $output;
}

function drawAddButton() {
    global $ADD_BASE_ID;
    $output  = '<button type="button" ';
    $output .=     'id="'.$ADD_BASE_ID.'_button" ';
    $output .=   'name="'.$ADD_BASE_ID.'_button" ';
    $output .=   'onclick="addItem()">';
    $output .= '<i class="fa fa-plus fa-fw"></i>';
    $output .= '</button>';
    return $output;
}

?>
