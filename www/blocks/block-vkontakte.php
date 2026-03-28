<?php

if (!defined('BLOCK_FILE')) {
header("Location: ../index.php");
exit;
}
$content=<<<BLOCKHTML
<noindex>
<script type="text/javascript" src="https://userapi.com/js/api/openapi.js?34"></script>

<!-- VK Widget -->
<div id="vk_groups"></div>
<script type="text/javascript">
VK.Widgets.Group("vk_groups", {mode: 0, width: "860", height: "290"}, 10795851);
</script>
</noindex>
BLOCKHTML;
?>