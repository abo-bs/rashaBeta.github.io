<?php require_once('includes/package.class.php');
$pdo = PDO2::getInstance();
$section = rawurldecode(strip_tags(addslashes($_GET['section'])));
$req1 = $pdo->prepare("SELECT COUNT(description.id) FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE Section = :section AND online = true");
try {
	$req1->execute(array(':section' => $section));
	$req1 = $req1->fetchColumn();
} catch(Exception $e) {
	$req1 = 0;
}
if($req1 < 1) {
	require_once('404.php');
	exit;
}
$nbr_pages = ceil($req1 / 50);
if(empty($_GET['page']) || !isset($_GET['page']))
	$page = 1;
elseif(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbr_pages)
	$page = $_GET['page'];
else
	$page = "Error";
if($page == "Error") {
	require_once('404.php');
	exit;
}
if($_SERVER['REQUEST_URI'] == '/section/'.rawurlencode($section).'/page/'.$page)
	$bonchemin = true;
elseif($_SERVER['REQUEST_URI'] == '/section/'.rawurlencode($section))
	$bonchemin = true;
else
	$bonchemin = false;
if($bonchemin) {
	$lang_user = translation();
	$precedent = $page - 1;
	$debut = $precedent * 50;
	$suivant = $page + 1;
	$pack_affiche = $req1 - $debut;
	$req = $pdo->prepare("SELECT description.id, Name, Author, Section, Version FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE Section = :section AND online = 1 LIMIT ".$debut.",50");
	$req->execute(array(':section' => $section));
	$req = $req->fetchAll(PDO::FETCH_ASSOC);
	$package_nbr = ($req1 < 2) ? strtolower(_('Package')) : strtolower(_('Packages'));
	$site_nom = config('nom');
	$site_url = config('url'); ?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $site_url; ?>css/style.min.css" />
		<link rel="shortcut icon" href="<?php echo $site_url; ?>images/favicon.ico" />
		<meta charset="UTF-8">
		<meta content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
		<meta name="title" content="<?php echo _('Section').' '.$section;if($page > 1)echo ' | '._('Page').' '.$page; ?> | <?php echo $site_nom; ?>">
		<meta name="author" content="<?php echo $site_nom; ?>">
		<meta property="og:title" content="<?php echo _('Section').' '.$section;if($page > 1)echo ' | '._('Page').' '.$page; ?> | <?php echo $site_nom; ?>">
		<meta property="og:image" content="<?php echo $site_url; ?>images/sections/<?php echo preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($section))).'.png'; ?>">
		<meta property="og:site_name" content="<?php echo $site_nom; ?>">
		<meta property="og:url" content="<?php echo $site_url; ?>section/<?php echo rawurlencode($section); ?>">
		<meta name="twitter:card" content="summary">
		<meta name="twitter:domain" content="<?php echo $site_nom; ?> ">
		<meta name="twitter:url" content="<?php echo $site_url; ?>section/<?php echo rawurlencode($section); ?>">
		<meta name="twitter:title" content="<?php echo _('Section').' '.$section;if($page > 1)echo ' | '._('Page').' '.$page; ?> | <?php echo $site_nom; ?>">
		<meta name="twitter:image" content="<?php echo $site_url; ?>images/sections/<?php echo preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($section))).'.png'; ?>">
		<title><?php echo _('Section').' '.$section;if($page > 1)echo ' | '._('Page').' '.$page; ?> | <?php echo $site_nom; ?></title>
	</head>
	<body style="user-select:none;-ms-user-select:none;-moz-user-select:none;-khtml-user-select:none;-webkit-user-select:none;-webkit-touch-callout:none">
		<div class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" style="border:0;margin:0;padding:15px 7.5px 0">
						<span class="sr-only">Menu</span>
						<span class="glyphicon glyphicon-search" style="color:#ccc;font-size:1.6em"></span>
					</button>
					<a class="navbar-brand glyphicon glyphicon-home" style="color:#ccc;font-size:1.6em;padding:15px 10px 0" href="<?php echo $site_url; ?>"></a>
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>news.php"></a>
					<a class="navbar-brand glyphicon glyphicon-cloud-download" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>top-download.php"></a>
					<a class="navbar-brand glyphicon glyphicon-star-empty" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>top-votes.php"></a>
					<a class="navbar-brand glyphicon glyphicon-folder-open" style="color:#fff;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>section/"></a>
					<a class="navbar-brand glyphicon glyphicon-user" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="<?php echo $site_url; ?>login.php"></a>
				</div>
				<div class="navbar-collapse collapse" style="border:0;box-shadow:none">
					<form action="<?php echo $site_url; ?>search.php"><fieldset class="navbar-form navbar-right" style="margin-right:0px;margin-left:0px;position:relative">
						<input class="form-control" type="text" name="s" placeholder="<?php echo _('Search').'...'; ?>" />
						<button type="submit" class="btn btn-default hidden-xs"><span class="glyphicon glyphicon-search"></span></button>
					</fieldset></form>
				</div>
			</div>
		</div>
		<div style="min-height:90%;padding:15px;padding-bottom:5px;background-color:#fff;max-width:1000px;margin-left:auto;margin-right:auto">
			<div class="media text-center">
				<?php echo '<span class="pull-left"><img width="60" height="60" style="background:rgba(0, 136, 255, 0.2);border-radius:13px;padding:2px;" class="media-object lazy" data-src="'.$site_url.'images/sections/'.preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($section))).'.png" alt="'.$section.'" /><noscript><img width="60" height="60" style="background:rgba(0, 136, 255, 0.2);border-radius:13px;padding:2px;" class="media-object" src="'.$site_url.'images/sections/'.preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($section))).'.png" alt="'.$section.'" /></noscript></span>
					<h1 class="media-heading ">'._('Section').' : '.$section.'</h1>
					'.$req1.' '.$package_nbr.'
				</div>'; ?>
				<span itemscope itemtype="http://www.data-vocabulary.org/breadcrumb">
					<meta content="<?php echo $site_url; ?>section/" itemprop="url"><meta itemprop="title" content="Sections">
				</span>
			<?php echo '<div class="list-group media"><div class="row">';
				$j = 0;
				foreach($req as $key) {
					$icone_paquet = (file_exists('images/debs/'.$key['id'].'.png')) ? $site_url.'images/debs/'.$key['id'].'.png' : $site_url.'images/sections/'.preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($key['Section']))).'.png';
					if(($j == 0 OR $j == 25) && $pack_affiche > 50)
						echo '<div class="col-sm-6">';
					elseif($j == 0)
						echo '<div class="col-sm-12">';
					$j++;
					echo '<a href="'.$site_url.'pack/'.$key['id'].'" class="list-group-item media text-center">
					<span class="pull-left"><img width="50" height="50" style="background:rgba(0,136,255,.2);border-radius:13px;padding:1px"  class="media-object lazy" data-src="'.$icone_paquet.'" alt="'.$key['Name'].'" /><noscript><img width="50" height="50" style="background:rgba(0,136,255,.2);border-radius:13px;padding:1px"  class="media-object" src="'.$icone_paquet.'" alt="'.$key['Name'].'" /></noscript></span>
					<div class="media-body">
						<h4 class="media-heading">'.$key['Name'].' <small>'.$key['Version'].'</small></h4>
						<span class="glyphicon glyphicon-user"></span> '.$key['Author'].' <span class="glyphicon glyphicon-folder-open"></span> '.$key['Section'].'
					</div>
					</a>';
					if(($j == 25 OR $j == 50) && $pack_affiche > 50)
						echo '</div>';
					elseif($j == count ($req))
						echo '</div>';

			 	}
			echo '</div></div>';
			echo '<div class="text-center"><ul class="pagination pagination-lg text-center">';
			if($page > 1)
				echo '<li><a href="'.$site_url.'section/'.rawurlencode($section).'/page/1">1</a></li>';
			if($precedent > 2)
				echo '<li class="disabled"><a>...</a></li>';
			if($page > 2)
				echo '<li><a href="'.$site_url.'section/'.rawurlencode($section).'/page/'.$precedent.'">'.$precedent.'</a></li>';
			echo '<li class="active"><a>'.$page.'</a></li>';
			if($nbr_pages > $suivant)
				echo '<li><a href="'.$site_url.'section/'.rawurlencode($section).'/page/'.$suivant.'">'.$suivant.'</a></li>';
			if($nbr_pages > $suivant + 1)
				echo '<li class="disabled"><a>...</a></li>';
			if($nbr_pages >= $suivant)
				echo '<li><a href="'.$site_url.'section/'.rawurlencode($section).'/page/'.$nbr_pages.'">'.$nbr_pages.'</a></li>';
			echo '</ul></div>';
			echo '<div class="lead"><p class="text-center">'.$page.' / '.$nbr_pages.'</p></div>'; ?>
		</div>
		<?php require_once('includes/front/footer.php'); ?>
		<script>$(document).on('ready',function(){$("img.lazy").lazy();});</script>
	</body>
</html>
<?php } else
	header('Location: /section/'.$section); ?>