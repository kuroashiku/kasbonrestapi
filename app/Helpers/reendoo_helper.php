<?php
    function formControl($label, $id, $height='0%')
    {
        echo '<tr height="'.$height.'">
            <td style="white-space:nowrap;vertical-align:middle" width="0%">'.$label.'</td>
            <td style="white-space:nowrap" width="100%"><input id="'.$id.'"></td>
        </tr>';
    }

    function formLabel($label, $height=0)
    {
        echo '<tr height="'.$height.'%" style="font-size:14px"><td></td><td>'.$label.'</td></tr>';
    }
?>