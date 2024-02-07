<?php
if(isset($_GET['page'])) {
    if($_GET['page'] == 'news') $_GET['page']  = 'index';
    if($_GET['page'] == 'knowledge') $_GET['page']  = 'index';
    $src = '../../' . $_GET['page'] . '?comand=editable';
}
?>

<iframe id="editableFrame" src="<?php echo $src; ?>" style="width:100%;height:calc(100vh - 62px);"></iframe>