<?php
if ($was_sent) {
    echo $success_view;
} else {
    echo $open_view;
    foreach ($fields_views as $field_view) {
        echo $field_view;
    }
    echo $close_view;
}
