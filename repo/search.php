<?php if(!empty($_SERVER['HTTP_REFERER']) && substr($_SERVER['HTTP_REFERER'], 8, strlen($_SERVER['SERVER_NAME'])) != $_SERVER['SERVER_NAME'])
	$_POST = array();
require_once('includes/package.class.php');
$pdo = PDO2::getInstance();
$lang_user = translation();
if(!empty($_GET['compatible'])) {
	switch($_GET['compatible']) {
		case 'iOS 9':
			$compatible_search = '%iOS 9%';
			break;
		case 'iOS 8':
			$compatible_search = '%iOS 8%';
			break;
		case 'iOS 7':
			$compatible_search = '%iOS 7%';
			break;
		case 'iOS 6':
			$compatible_search = '%iOS 6%';
			break;
		case 'iOS 5':
			$compatible_search = '%iOS 5%';
			break;
		case 'iOS 4':
			$compatible_search = '%iOS 4%';
			break;
		default:
			$compatible_search = 'all';
	}
} else
	'all';
$compatible_req = (!empty($compatible_search) && $compatible_search != 'all') ? '  AND compatible_ios LIKE "'.$compatible_search.'"' : '';

$section_search = (!empty($_GET['section'])) ? rawurldecode(addslashes(trim($_GET['section']))) : 'all';
$section_req = (!empty($section_search) && $section_search != 'all') ? ' AND description.Section = "'.$section_search.'"' : '';

$search = (!empty($_GET['s'])) ? preg_replace(array('#\%#', '#\_#'), array('\%', '\_'), addslashes(trim($_GET['s']))) : '';

$count = $pdo->prepare('SELECT COUNT(description.id) FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE online = 1 AND Name LIKE :search'.$section_req.$compatible_req);
$count->execute(array(':search' => '%'.$search.'%'));
$count = $count->fetchColumn();
$fin = ceil($count / 50);

$page = (!empty($_GET['page']) && is_numeric($_GET['page']) && ($_GET['page'] - 1) < $fin && $_GET['page'] > 0) ? preg_replace("/[^0-9]/", '', $_GET['page']) : 1;

$debut = ($page - 1) * 50;
$resultat = ($count < 2) ? _('result') : _('results');
$precedent = $page - 1;
$suivant = $page + 1;

$req = $pdo->prepare('SELECT description.id, Name, Version, Author, Section FROM description INNER JOIN description_meta ON description.id = description_meta.id WHERE online = 1 AND Name LIKE :search'.$section_req.$compatible_req.' ORDER BY Name ASC LIMIT '.$debut.', 50');
$req->execute(array(':search' => '%'.$search.'%'));
$req = $req->fetchAll(PDO::FETCH_ASSOC);

$pack_affiche = count($req);

$sections = $pdo->prepare("SELECT DISTINCT(Section) FROM description ORDER BY Section ASC");
$sections->execute();
$sections = $sections->fetchAll(PDO::FETCH_ASSOC);
$site_url = config('url');
$site_nom = config('nom');
$pdo = PDO2::closeInstance(); ?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
		<title><?php echo _('Search'); ?> | <?php echo $site_nom; ?></title>
		<link rel="shortcut icon" href="images/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="css/style.min.css" />
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-static-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" style="border:0;margin:0;padding:15px 7.5px 0">
						<span class="sr-only">Menu</span>
						<span class="glyphicon glyphicon-search" style="color:#fff;font-size:1.6em"></span>
					</button>
					<a class="navbar-brand glyphicon glyphicon-home" style="color:#ccc;font-size:1.6em;padding:15px 10px 0" href="./"></a>
					<a class="navbar-brand glyphicon glyphicon-refresh" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="news.php"></a>
					<a class="navbar-brand glyphicon glyphicon-cloud-download" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="top-download.php"></a>
					<a class="navbar-brand glyphicon glyphicon-star-empty" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="top-votes.php"></a>
					<a class="navbar-brand glyphicon glyphicon-folder-close" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="section/"></a>
					<a class="navbar-brand glyphicon glyphicon-user" style="color:#ccc;font-size:1.6em;padding:15px 7.5px 0" href="login.php"></a>
				</div>
				<div class="navbar-collapse collapse" style="border:0;box-shadow:none">
					<form action="search.php"><fieldset class="navbar-form navbar-right" style="margin-right:0px;margin-left:0px;position:relative">
						<input class="form-control" type="text" name="s" placeholder="<?php echo _('Search').'...'; ?>" />
						<button type="submit" class="btn btn-default hidden-xs"><span class="glyphicon glyphicon-search"></span></button>
					</fieldset></form>
				</div>
			</div>
		</div>
		<div style="min-height:90%;padding:15px;padding-bottom:5px;background-color:#fff;max-width:1000px;margin-left:auto;margin-right:auto">
			<h3 class="text-center"><span class="glyphicon glyphicon-search"></span> <?php echo _('Search').' <small>'.$count.' '.$resultat.'</small></span>'; ?></h3>
			<form class="navbar-form text-center" style="box-shadow:0 0 0">
				<input id="search" autofocus style="margin-bottom:10px;user-select:text;-ms-user-select:text;-moz-user-select:text;-khtml-user-select:text;-webkit-user-select:text;-webkit-touch-callout:text" type="text" class="form-control" placeholder="<?php echo _('Search'); ?>..." name="s" value="<?php echo htmlspecialchars(stripslashes(preg_replace('#\\\%#', '%', $search))); ?>" />
				<select style="margin-bottom:10px" name="section" class="form-control">
					<option value="all"><?php echo _('All sections'); ?></option>
					<?php foreach($sections as $section) {
						echo '<option';
						if($section_search == $section['Section'])
							echo ' selected';
						echo '>'.$section['Section'];
						echo '</option>';
					} ?>
				</select>
				<select style="margin-bottom:10px" name="compatible" class="form-control">
					<option value="all"><?php echo _('All iOS'); ?></option>
					<option<?php if($compatible_search == '%iOS 9%')echo ' selected'; ?>>iOS 9</option>
					<option<?php if($compatible_search == '%iOS 8%')echo ' selected'; ?>>iOS 8</option>
					<option<?php if($compatible_search == '%iOS 7%')echo ' selected'; ?>>iOS 7</option>
					<option<?php if($compatible_search == '%iOS 6%')echo ' selected'; ?>>iOS 6</option>
					<option<?php if($compatible_search == '%iOS 5%')echo ' selected'; ?>>iOS 5</option>
					<option<?php if($compatible_search == '%iOS 4%')echo ' selected'; ?>>iOS 4</option>
				</select>
				<button style="margin-bottom:10px" type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span></button>
			</form>
			<?php if(!empty($req)) {
				if($pack_affiche == 50)
					echo '<div class="list-group media"><div class="row">';
				else
					echo '<div style="margin-top:20px">';
				$j = 0;
				foreach($req as $key) {
					$icone_paquet = (file_exists('images/debs/'.$key['id'].'.png')) ? 'images/debs/'.$key['id'].'.png' : 'images/sections/'.preg_replace("/[\/_|+ -]+/", '-', strtolower(trim($key['Section']))).'.png';
					if(($j == 0 OR $j == 25) && $pack_affiche == 50)
						echo '<div class="col-sm-6">';
					$j++;
					$nom = htmlspecialchars($key['Name']);
					$version = $key['Version'];
					echo '<a href="pack/'.$key['id'].'" class="list-group-item media text-center">
					<span class="pull-left"><img width="50" height="50" style="background:rgba(0,136,255,.2);border-radius:13px;padding:1px" class="media-object lazy" data-src="'.$icone_paquet.'" alt="'.$nom.' - '.$version.' /"><noscript><img width="50" height="50" style="background:rgba(0,136,255,.2);border-radius:13px;padding:1px" class="media-object" src="'.$icone_paquet.'" alt="'.$nom.' - '.$version.'" /></noscript></span>
					<div class="media-body">
						<h4 class="media-heading">'.$nom.' <small>'.$version.'</small></h4>
						<span class="glyphicon glyphicon-user"></span> '.$key['Author'].' <span class="glyphicon glyphicon-folder-open"></span> '.$key['Section'].'
					</div>
					</a>';
					if(($j == 25 OR $j == 50) && $pack_affiche == 50)
						echo '</div>';
				}
				if($pack_affiche == 50)
					echo '</div></div>';
				else
					echo '</div>';
				echo '<div class="text-center"><ul class="pagination pagination-sm">';
				if($page > 1)
					echo '<li><a href="?section='.$section_search.'&page=1&s='.rawurlencode(stripslashes($search)).'">1</a></li>';
				if($precedent > 2)
					echo '<li class="disabled"><a>...</a></li>';
				if($page > 2)
					echo '<li><a href="?section='.$section_search.'&page='.$precedent.'&s='.rawurlencode(stripslashes($search)).'">'.$precedent.'</a></li>';
				echo '<li class="active"><a>'.$page.'</a></li>';
				if($fin > $suivant)
					echo '<li><a href="?section='.$section_search.'&page='.$suivant.'&s='.rawurlencode(stripslashes($search)).'">'.$suivant.'</a></li>';
				if($fin > $suivant + 1)
					echo '<li class="disabled"><a>...</a></li>';
				if($fin >= $suivant)
					echo '<li><a href="?section='.$section_search.'&page='.$fin.'&s='.rawurlencode(stripslashes($search)).'">'.$fin.'</a></li>';
				echo '</ul></div>';
				echo '<div class="lead"><p class="text-center">'.$page.' / '.$fin.'</p></div>';
			} else
				echo '<div style="margin:20px"><p class="lead text-center alert alert-danger">'._('No package found').'</p></div>'; ?>
		</div>
		<?php require_once('includes/front/footer.php'); ?>
		<script>$(document).on('ready',function(){$("img.lazy").lazy();});</script>
	</body>
</html>