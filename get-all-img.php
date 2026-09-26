<?php header('content-type: application/json'); $data = array('images' => array());
//foreach (glob('imgdata/*.*') as $echo) if (preg_match('/([^\\/]+)$/D', $echo,
//$matches)) if(!str_starts_with($matches[1], '.')) $data['images'][ ] = $echo;
echo json_encode($data);
