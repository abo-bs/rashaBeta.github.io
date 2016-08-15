<?php require_once('includes/session.class.php');
$membre = new Session();
$idpaquet = trim($_GET['pack']);
require_once('includes/package.class.php');
$paquet = new Paquet($idpaquet);
if($paquet->verifier_fiche()) {
	if($_SERVER['REQUEST_URI'] === '/pack/'.$_GET['pack']) {
		$lang_user = translation();
		$paquet->tracker();
		$votes = $paquet->rating_bar(_('vote'), _('votes'));
		$total_download = number_format($paquet->package_control('total_download'), 0, ',', ' ');
		$site_url = config('url');
		$site_nom = config('nom');
		$infos = $paquet->package_control(array('Name', 'Version', 'Description', 'Section', 'Author', 'Size', 'changelog', 'compatible_device', 'compatible_ios', 'online', 'date'));
		$cate = $paquet->icone_paquet();

		$device = type_device();
		$ios = type_ios();
		$total_compatible_ios = count($compatible_ios);
		$total_compatible_device = count($compatible_device);
		$compatible_device = unserialize($infos['compatible_device']);
		$compatible_ios = unserialize($infos['compatible_ios']);
		$device = type_device();
		$ios = type_ios();
		$compatible_device = unserialize($infos['compatible_device']);
		$compatible_ios = unserialize($infos['compatible_ios']);
		$total_compatible_ios = count($compatible_ios);
		$total_compatible_device = count($compatible_device);
		if(!empty($compatible_device) && !empty($compatible_ios) && $total_compatible_ios > 0 && $total_compatible_device > 0) {
			if(in_array(trim($device), $compatible_device)) {
				if(in_array('iOS '.substr($ios, 0, 1), $compatible_ios)) {
					$class_compatible = 'bg-success';
					$texte_compatible = _('Your device and iOS version are compatible !');
				} else {
					$class_compatible = 'bg-danger';
					$texte_compatible = _('WARNING: Not compatible with your iOS version ');
				}
			} else {
				$class_compatible = 'background-danger';
				$texte_compatible = _('WARNING: Not compatible with your device !');
			}
			$compatible = '<div class="text-center '.$class_compatible.'"><small>'.$texte_compatible.'</small></div>';
		} else
			$compatible = '';
		if(!empty($compatible_device)) {
			$device = '<li>';
			for($i=0;$i<count($compatible_device);$i++) {
				if($i == 0 && count($compatible_device) > 1 )
					$device .= '<span class="glyphicon glyphicon-phone"></span> '.$compatible_device[$i]. ' - ';
				elseif($i == 0 && count($compatible_device) == 1 )
					$device .= '<span class="glyphicon glyphicon-phone"></span> '.$compatible_device[$i];
				elseif($i == (count($compatible_device) - 1))
					$device .= $compatible_device[$i];
				else
					$device .= $compatible_device[$i].' - ';
			}
			$device .= '</li>';
		} else
			$device = '';
		if(!empty($compatible_ios)) {
			$ios= '<li>';
			for($i=0;$i<count($compatible_ios);$i++) {
				if($i == 0 && count($compatible_ios) > 1 )
					$ios.= '<span class="glyphicon glyphicon-cog"></span> '.$compatible_ios[$i]. ' - ';
				elseif($i == 0 && count($compatible_ios) == 1 )
					$ios.= '<span class="glyphicon glyphicon-cog"></span> '.$compatible_ios[$i].'</li>';
				elseif($i == (count($compatible_ios) - 1))
					$ios.= $compatible_ios[$i];
				else
					$ios.= $compatible_ios[$i].' - ';
			}
			$ios.= '</li>';
		} else
			$ios= ''; ?>
<!DOCTYPE html>
<html itemscope itemtype="http://schema.org/Article" prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<head>
		<link rel="shortcut icon" href="../images/favicon.ico" />
		<link rel="stylesheet" type="text/css" media="screen" href="../css/style.min.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="../css/ratings.css" />
		<meta charset="UTF-8">
		<meta name="title" content="<?php echo htmlspecialchars($infos['Name'].' '.$infos['Version']); ?> | <?php echo $site_nom; ?>">
		<meta name="description" content="<?php echo htmlspecialchars($infos['Name'].' '.$infos['Version']).' : '.htmlspecialchars($infos['Description']); ?>">
		<meta name="author" content="<?php echo $site_nom; ?>">
		<meta itemprop="name" content="<?php echo htmlspecialchars($infos['Name'].' '.$infos['Version']); ?> | <?php echo $site_nom; ?>">
		<meta itemprop="description" content="<?php echo htmlspecialchars($infos['Name'].' '.$infos['Version']).' : '.htmlspecialchars($infos['Description']); ?>">
		<meta itemprop="image" content="<?php echo $cate; ?>">
		<meta itemprop="url" content="<?php echo $site_url; ?>pack/<?php echo $idpaquet; ?>">
		<meta property="og:url" content="<?php echo $site_url; ?>pack/<?php echo $idpaquet; ?>">
		<meta property="og:title" content="<?php echo htmlspecialchars($infos['Name'].' '.$infos['Version']); ?> - <?php echo $site_nom; ?>">
		<meta property="og:description" content="<?php echo htmlspecialchars($infos['Name'].' '.$infos['Version']).' : '.htmlspecialchars($infos['Description']); ?>">
		<meta property="og:image" content="<?php echo $cate; ?>">
		<meta property="og:site_name" content="<?php echo $site_nom; ?>">
		<meta name="twitter:card" content="summary">
		<meta name="twitter:domain" content="<?php echo $site_nom; ?>">
		<meta name="twitter:url" content="<?php echo $site_url; ?>pack/<?php echo $idpaquet; ?>">
		<meta name="twitter:title" content="<?php echo htmlspecialchars($infos['Name'].' '.$infos['Version']); ?> | <?php echo $site_nom; ?>">
		<meta name="twitter:description" content="<?php echo htmlspecialchars($infos['Name'].' '.$infos['Version']).' : '.htmlspecialchars($infos['Description']); ?>">
		<meta name="twitter:image" content="<?php echo $cate; ?>">
		<meta itemprop="datePublished" content="<?php echo date('Y-m-d', strtotime($infos['date'])); ?>">
		<meta content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport"> 
		<title><?php echo htmlspecialchars($infos['Name'].' '.$infos['Version']); ?> | <?php echo $site_nom; ?></title>
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" style="border:0;margin:0;padding:15px 7.5px 0">
						<span class="sr-only">Menu</span>
						<span class="glyphicon glyphicon-search" style="color:#ccc;font-size:1.6em"></span>
					</button>
					<a class="navbar-brand glyphicon glyphicon-home" style="color:#ccc;font-size:1.6em;padding:15px 10px 0;" href="../"></a>
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="../news.php"></a>
					<a class="navbar-brand glyphicon glyphicon-cloud-download" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="../top-download.php"></a>
					<a class="navbar-brand glyphicon glyphicon-star-empty" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="../top-votes.php"></a>
					<a class="navbar-brand glyphicon glyphicon-folder-close" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="../section/"></a>
					<a class="navbar-brand glyphicon glyphicon-user" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0;" href="../login.php"></a>
				</div>
				<div class="navbar-collapse collapse" style="border:0;box-shadow:none">
					<form action="../search.php"><fieldset class="navbar-form navbar-right" style="margin-right:0px;margin-left:0px;position:relative">
						<input class="form-control" type="text" name="s" placeholder="<?php echo _('Search').'...'; ?>" />
						<button type="submit" class="btn btn-default hidden-xs"><span class="glyphicon glyphicon-search"></span></button>
					</fieldset></form>
				</div>
			</div>
		</div>
		<div style="min-height:90%;padding:15px;padding-bottom:5px;background-color:#fff;max-width:1000px;margin-left:auto;margin-right:auto">
			<div class="media text-center">
				<span class="pull-left" style="position:relative">
					<?php if(!$infos['online'])
						echo '<span style="background:rgba(255,0,0,.5);position:absolute;top:0.7em;-webkit-transform:rotate(-45deg);-moz-transform:rotate(-45deg);line-height:1.2em;max-width:100%;min-width:6em;text-align:center;left:-1.7em;opacity:.85;text-shadow: 1px 2px 3px #fff;color:#000;font-weight:bold;margin:2px">Offline</span>'; ?>
					<img itemprop="image" width="60" height="60" style="border-radius:13px;height:60px;width:60px;padding:2px" class="media-object" src="<?php echo $cate.'" alt="'.htmlspecialchars($infos['Name']).' - '.htmlspecialchars($infos['Version']); ?>" />
				</span>
				<div class="media-body">
					<h1 class="media-heading page-header">
						<span itemprop="headline"><?php echo htmlspecialchars($infos['Name']); ?></span>
						<small> <?php echo htmlspecialchars($infos['Version']); ?></small>
					</h1>
				</div>
			</div>
			<?php echo '<ul class="text-center list-inline">
				<li><h5><span class="glyphicon glyphicon-user"></span> '.$infos['Author'].'</h5></li>
				<li><h5><span class="glyphicon glyphicon-folder-open"></span> <a href="../section/'.rawurlencode($infos['Section']).'" title="'.$infos['Section'].'"><span itemprop="articleSection">'.$infos['Section'].'</span></a></h5>
					<span itemscope itemtype="http://www.data-vocabulary.org/breadcrumb"><meta content="'.$site_url.'section/" itemprop="url"><meta itemprop="title" content="Sections">
						<span itemprop="child" itemscope itemtype="http://www.data-vocabulary.org/breadcrumb"><meta content="'.$site_url.'section/'.rawurlencode($infos['Section']).'" itemprop="url"><meta itemprop="title" content="'.$infos['Section'].'"></span>
					</span>
				</li>
				<li><h5><span class="glyphicon glyphicon-cloud-download"></span> '.$total_download.'</h5></li>
				<li><h5><span class="glyphicon glyphicon-compressed"></span> '.taille_fichier($infos['Size']).'</h5></li>
				'.$device.'
				'.$ios.'
			</ul><hr />';
			if(!empty($_SERVER['HTTP_USER_AGENT']) && preg_match('/'. implode( '|', array('Cydia') ) . '/i', $_SERVER['HTTP_USER_AGENT'])) {
				echo $compatible;
			}
			echo $votes;
			if(!$infos['online'])
				echo '<p class="text-center" style="color:#f00">'._('This package is temporarily offline on the repository.').'</p>';
			if($i= $paquet->screen_paquet(false)){
				$tab = $paquet->screen_paquet(true);
				echo '<div style="min-width:280px" id="carousel-generic" class="carousel slide" data-ride="carousel">';
				if($i > 1) {
					echo '<ol class="carousel-indicators">';
					foreach($tab as $number => $image) {
						echo '<li data-target="#carousel-generic" data-slide-to="'.$number.'" ';
						if($number == 0)
							echo 'class="active"';
							echo '></li>';
					}
					echo '</ol>';
				}
				echo '<div class="carousel-inner text-center">';
				foreach($tab as $number => $image) {
					echo '<div class="item';
					if($number == 0)
						echo ' active';
					echo '" style="height:450px;"><img class="lazy" data-src="'.$image.'" style="margin:auto;max-height:450px;border-radius:5px;box-shadow:0 0 6px rgba(0,0,0,.6)" alt="'.htmlspecialchars($infos['Name']).' - '.htmlspecialchars($infos['Version']).'" /><noscript><img src="'.$image.'" style="margin:auto;max-height:450px;border-radius:5px;box-shadow:0 0 6px rgba(0,0,0,.6)" alt="'.htmlspecialchars($infos['Name']).' - '.htmlspecialchars($infos['Version']).'" /></noscript></div>';
				}
				echo '</div>';
				if($i > 1)
					echo '<a class="left carousel-control" href="#carousel-generic" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a class="right carousel-control" href="#carousel-generic" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>';
				echo '</div>';
			}
			echo '<iframe onLoad="iframe();" src="/dpt-desc.php?id='.$idpaquet.'" id="iframe" name="iframe" frameborder="0" style="border:0;frameborder:0;overflow-x:hidden;overflow-y:scroll;width:100%"></iframe>';
			if($infos['changelog'] != false) {
				echo '<hr /><div class="lead"><h2>'._('Recent changes').'</h2><iframe onLoad="iframe_changelog();" src="/dpt-desc.php?id='.$idpaquet.'&changelog" id="iframe_changelog" name="iframe_changelog" frameborder="0" style="border:0;frameborder:0;overflow-x:hidden;overflow-y:scroll;width:100%"></iframe><script>function iframe_changelog(){document.getElementById("iframe_changelog").style.height = document.getElementById("iframe_changelog").contentDocument.body.offsetHeight+20+"px";}</script></div>';
			}
			echo '<p class="page-header share">
				<a href="#" onClick="window.open(\'http://www.facebook.com/share.php?u='.rawurlencode(utf8_encode($site_url.'pack/'.$idpaquet)).'\',\'_blank\',\'toolbar=0, location=0, directories=0, status=0, scrollbars=0, resizable=0, copyhistory=0, menuBar=0, width=500, height=300\');return(false)" rel="nofollow" title="Share on Facebook"><img src="'.$site_url.'images/facebook.png" alt="Facebook" width="32" height="32" style="height:32px;width:32px" title="Share on Facebook" /></a>
				<a href="#" onClick="window.open(\'https://twitter.com/intent/tweet?text='.rawurlencode(utf8_encode(htmlspecialchars($infos['Name']).' '.htmlspecialchars($infos['Version']).' - '.$site_nom.' - #cydia')).'&amp;url='.rawurlencode(utf8_encode($site_url.'pack/'.$idpaquet)).'\',\'_blank\',\'toolbar=0, location=0, directories=0, status=0, scrollbars=0, resizable=0, copyhistory=0, menuBar=0, width=500, height=300\');return(false)" target="blank" rel="nofollow" title="Share on Twitter"><img src="'.$site_url.'images/twitter.png" alt="Twitter" width="32" height="32" style="height:32px;width:32px" title="Share on Twitter" /></a>
				<a href="#" onClick="window.open(\'https://plus.google.com/share?url='.rawurlencode(utf8_encode($site_url.'pack/'.$idpaquet)).'\',\'_blank\',\'toolbar=0, location=0, directories=0, status=0,&nbsp;scrollbars=0, resizable=0, copyhistory=0, menuBar=0, width=500, height=300\');return(false)" target="blank" rel="nofollow" title="Share on Google +"><img src="'.$site_url.'images/google.png" alt="Google +" width="32" height="32" style="height:32px;width:32px" title="Share on Google +" /></a>
				<a href="mailto:?subject='.rawurlencode(htmlspecialchars($infos['Name'])).'%20-%'.$site_nom.'&amp;body='.rawurlencode(utf8_encode(htmlspecialchars($infos['Name']).' '.htmlspecialchars($infos['Version']).' - '.htmlspecialchars($infos['Description']).' - '.$site_url.'pack/'.$idpaquet)).'" rel="nofollow" target="blank"><img title="Share by email" src="'.$site_url.'images/mail.png" alt="Mail" width="32" height="32" style="height:32px;width:32px" /></a>
			</p>'; ?>
		</div>
		<?php require_once('includes/front/footer.php'); ?>
		<script async type="text/javascript" src="../js/behavior.js"></script>
		<script async type="text/javascript" src="../js/favorite.js"></script>
		<script>function iframe(){document.getElementById("iframe").style.height = document.getElementById("iframe").contentDocument.body.offsetHeight+20+"px";}</script>
		<script>$(document).on('ready',function(){$("img.lazy").lazy();});</script>
	</body>
</html>
<?php } else
		header('Location: pack/'.$_GET['pack']);
} else
	require_once('404.php'); ?>