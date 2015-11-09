<?php

require_once(dirname(__FILE__).'/../module.inc.php');
$listId = io::get('id');

// Get the list using the listId (passed by parameters)
$list = $api->contactslist(array('method' => 'VIEW', 'ID'=> $listId))->Data[0];

// to get all contacts of the list, we use Limit = -1
$contacts = $api->contact(array('method' => 'GET',  'ContactsList'=> $listId, 'Limit' => '-1'));
$submitted = io::post('submit');

$errors = array();
$updated = false;
if($submitted) {
  $label = io::post('label');
  $listId = io::post('id');
  if(empty($label)) {
    $errors[] = 'Veuillez remplir tous les champs obligatoires';
  }

  if(empty($errors)) {
    $response = $api->contactslist(array('method' => 'PUT', 'unique' => $listId, 'Name' => $label));
    if(isset($api->_response_code) && $api->_response_code === MailjetAPI::MAILJET_STATUS_CODE_OK_PUT){
      $updated = true;
    }
  }
}
else {
  if($list) {

    // List's id (not listId), make with numbers and characters
    $name = $list->Address;

    // List's name
    $label = $list->Name;
  }
}

?>
<!DOCTYPE html>
<html lang="en">
  <?php include dirname(__FILE__).'/../partials/head.php'; ?>

  <body>
    <div id="header">
      <h1><a href="index.php"><?php echo $cms_module->getLabel($cms_language); ?></a></h1>
      <a id="menu-trigger" href="#"><i class="icon-align-justify"></i></a>
    </div>
    <?php include dirname(__FILE__).'/../partials/sidebar.php'; ?>

    <div id="content">
      <div id="content-header">
        <h1>Listes</h1>
      </div>
      <div id="breadcrumb">
        <a href="../index.php" title="Go to Home" class="tip-bottom"><i class="icon-home"></i>Accueil du module</a>
        <a href="index.php">Listes</a>
        <a href="view.php?id=<?php echo $listId ?>" class="current"><?php echo $label?></a>
      </div>
      <?php if($list):?>
        <div class="row">
          <div class="col-md-12 form-container">
            <?php if ($updated): ?>
              <div class="alert alert-success">
                <strong>La liste a été mise à jour.</strong>
              </div>
            <?php endif ?>
            <div class="widget-box">
              <div class="widget-title">
                <span class="icon">
                  <i class="icon-th"></i>
                </span>
                <h5>Modification de liste</h5>
              </div>
              <div class="widget-content nopadding">
                <?php foreach ($errors as $message): ?>
                  <div class="alert alert-danger">
                    <button class="close" data-dismiss="alert">×</button>
                    <strong><?php echo $message;?>
                  </div>
                <?php endforeach ?>
                <form action="" method="post" class="form-horizontal">
                  <div class="form-group">
                    <label class="control-label" for="label">Titre <span class="mandatory">*</span></label>
                    <div class="controls">
                      <input type="text" name="label" class="form-control input-sm" required value="<?php echo $label?>">
                      <span class="help-block">Le titre de cette liste.</span>
                    </div>
                  </div>
                  <div class="form-actions">
                    <input type="hidden" name="id" value="<?php echo $listId ?>">
                    <button type="submit" name="submit" value="1" class="btn btn-primary">Sauvegarder</button> <a class="btn btn-inverse" href="index.php">Annuler</a>
                  </div>
                </form>
              </div>
            </div>
          </div><!-- end .form-container -->
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="widget-box">
              <div class="widget-title">
                  <span class="icon">
                    <i class="icon-th"></i>
                  </span>
                  <h5>Abonnés</h5>
                </div>
              <div class="widget-content nopadding">
                <table class="table table-bordered table-striped table-hover data-table">
                  <thead>
                    <tr>
                      <th>Adresse</th>
                      <th>Emails envoyés</th>
                      <th>Date d'abonnement</th>
                      <th>Dernière activité</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($contacts->Data as $contact): ?>
                    <tr>
                      <td><?php echo $contact->Email?></td>
                      <td><?php echo $contact->DeliveredCount?></td>
                      <td><?php echo date_create($contact->CreatedAt)->format('d/m/Y')?></td>
                      <td><?php echo date_create($contact->LastActivityAt)->format('d/m/Y')?></td>
                    </tr>
                  <?php endforeach ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="widget-box collapsible">
              <div class="widget-title">
                <a href="#collapse-form" data-toggle="collapse">
                  <span class="icon"><i class="icon-remove"></i></span>
                  <h5>Code d'inscription à une liste</h5>
                </a>
              </div>
              <div class="collapse" id="collapse-form">
                  <div class="widget-content">
                    <div class="alert alert-info">
                      <i class="icon icon-question-sign icon-2x"></i>
                      <strong>Comment inscrire des utilisateurs ?</strong>
                      <p>
                        Le code ci-dessous, placé dans une rangée, va proposer aux visiteurs un formulaire d'inscription à cette liste.
                      </p>
                    </div>
                    <pre><code class="language-php">
&lt;?php<br />
$contact = array(
  "Email" => io::post('email'),
  "Name" => io::post('name'),
);
$params = array(
  "method" => "POST",
  "ID" => "<?php echo $list->ID; ?>",
);
$params = array_merge($params, $contact);
$mailjet = new CMS_module_mailjet();
$api = $mailjet->getAPI();
$result = $api->contactslistManageContact($params);
if($api->_response_code == MailjetApi::MAILJET_STATUS_CODE_OK_POST){
  $created = true;
  $id = $result->Data[0]->ContactID;
  // le contact a été créé
}else{
  // Erreur lors de la création du contact
}
?&gt;<br />
&lt;form action="{page:self:url}" method="post"&gt;
    &lt;label for="label"&gt;Email :&lt;/label&gt;
    &lt;input type="text" name="email" required="required" value="" /&gt;
    &lt;input type="text" name="name" required="required" value="" /&gt;
    &lt;button type="submit" name="submit" value="1" &gt;Je m'inscris&lt;/button&gt;
&lt;/form&gt;
                    </code></pre>
                  </div>
              </div>
            </div>
          </div>
        </div>
      <?php else:?>
        <div class="row">
          <div class="col-md-12">
            <div class="alert alert-danger">
              <button class="close" data-dismiss="alert">×</button>
              <strong>Erreur!</strong> Récupération des informations impossible.
            </div>
          </div>
        </div>
      <?php endif;?>
    </div> <!-- end #content  -->
    <?php include dirname(__FILE__).'/../partials/scripts.php'; ?>
  </body>
</html>
