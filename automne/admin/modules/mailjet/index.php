<?php
require_once(dirname(__FILE__).'/module.inc.php');

$mailjetKey    = $cms_module->getParameters('MAILJET_API_KEY');
$mailjetSecret = $cms_module->getParameters('MAILJET_SECRET_KEY');
$errors = array();
if(empty($mailjetKey) || empty($mailjetSecret)) {
	$errors[] = 'Les paramètres de l\'API ne sont pas renseignés. Veuiller éditer les paramétres du module avant de l\'utiliser.';
}
?>
<!DOCTYPE html>
<html lang="en">
	<?php include 'partials/head.php'; ?>

	<body>
		<div id="header">
			<h1><a href="index.php"><?php echo $cms_module->getLabel($cms_language); ?></a></h1>
			<a id="menu-trigger" href="#"><i class="icon-align-justify"></i></a>
		</div>
		<?php include 'partials/sidebar.php'; ?>


		<div id="content">
			<div id="content-header">
				<h1>Mailjet</h1>
			</div>
			<div id="breadcrumb">
				<a href="#" title="Go to Home" class="tip-bottom"><i class="icon-home"></i>Accueil du module</a>
			</div>

				<?php if (empty($errors)): ?>
					<div class="row">
						<div class="col-md-12">
							<div class="widget-box">
		            <div class="widget-title">
		              <span class="icon">
		                <i class="icon-question-sign"></i>
		              </span>
		              <h5>Aide</h5>
		            </div>
		            <div class="widget-content">
									<h3>
										Bienvenue dans le module Mailet.
									</h3>
									<p class="highlight highlight-info">
										Pour démarrer, créez une ou plusieurs listes de diffusions. Si vous avez déjà des listes disponibles sur Mailjet, celles-ci seront directement accessibles.
										Une fois vos listes créées, vous pourrez gérer vos campagnes.
										<strong>Si vous avez déjà créé des campagnes chez Mailjet vous pourrez les consulter depuis le module mais pas les envoyer / rédiger.</strong>
									</p>
									<p>
										<h3>Utilisation du module</h3>
										<h4>1 - Création d'une page</h4>
										<p class="highlight">Vous créez une page Automne utilisant un modéle de page adapté à l'envoi de mail.
										Pour plus d'informations concernant les bonnes pratiques pour les modèles de mail, la lecture de cette série d'article est recommandée : <a target="_blank" href="http://www.pompage.net/traduction/emails-reactifs-1-commencer">Concevoir des e-mails réactifs</a></p>

										<h4>2 - Création d'une campagne</h4>
										<p class="highlight">Une fois votre page créée, vous pouvez créer une campagne, qui sera alors en mode <span class="label label-default">Brouillon</span>.</p>

										<h4>3 - Rédaction et tests de la campagne</h4>
										<p class="highlight">Sur les campagnes en mode <span class="label label-default">Brouillon</span> vous pouvez accédez à la partie "Rédaction" et renseigner l'identifiant de la page. Si la page est conforme au format attendu, vous pouvez alors tester votre email et consulter une version de prévisualisation.</p>

										<h4>4 - Envoi de la campagne</h4>
										<p class="highlight">Une fois votre contenu rédigé et envoyé à Mailjet, vous pouvez préparer l'envoi. Deux options seront disponibles : envoi immédiat ou envoi programmé.
											Votre campagne passe alors en statut <span class="label label-success">Envoyée</span> ou <span class="label label-warning">Programmée</span>
										Un seul envoi est possible par campagne.</p>
										<h4>5 - Archivage de la campagne</h4>
										<p class="highlight">A tout moment vous pouvez archiver une campagne. Son statut passe alors à <span class="label label-info">Archivée</span> et aucune action ne peut être menée dessus.
										Un seul envoi est possible par campagne.</p>
									</p>
			          </div>
		          </div>
	        	</div>
	        </div>
				<?php else: ?>
					<div class="row">
						<div class="col-md-12">
							<div class="widget-content nopadding">
								<?php foreach ($errors as $message): ?>
									<div class="alert alert-danger">
										<strong><?php echo $message;?></strong>
									</div>
								<?php endforeach ?>
								<div class="alert alert-info">
										<i class="icon icon-question-sign icon-2x"></i>
										<strong>Comment récupérer mes identifiants ?</strong>
										<p>
											Pour bien fonctionner, ce module a besoin de connaitre votre clé publique et votre clé secréte Mailjet. Pour connaitre ces clés, connectez-vous à Mailjet et cliquez sur "Mon compte" puis "Clé API principale" dans la section REST API.
										</p>
									</div>
							</div>
						</div>
					</div>
				<?php endif ?>
			</div>
		</div>
   	<?php include 'partials/scripts.php'; ?>
	</body>
</html>
