<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\authclient\OAuth2;
use app\models\ContactForm;
use app\models\FileEntry;
use app\models\User;
use yii\imagine\Image;

class FileController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        
        return parent::beforeAction($action);
    }
    
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionUpload()
    {
        error_reporting(E_ERROR);

        //Check for token
        if(isset($_POST['secret']) && isset($_POST['id']))
        {
			     
			$identity = User::find()
				->where(['ms_id' => $_POST['id']])
                ->andWhere(['auth_key' => $_POST['secret']])
				->one();
			
			Yii::$app->user->login($identity);
			
            //Checks if token is valid
            if(!Yii::$app->user->isGuest)
            {
                //Prepare for upload
                $combo = Yii::$app->getSecurity()->generateRandomString(10);
                $comboCheck = FileEntry::find()
                    ->where(['combo' => $combo])
                    ->exists();
                    
                if($comboCheck == true)
                {
                    $combo = Yii::$app->getSecurity()->generateRandomString(10);
                }
                
                $public_combo = Yii::$app->getSecurity()->generateRandomString(10);
                $public_comboCheck = FileEntry::find()
                    ->where(['public_combo' => $public_combo])
                    ->exists();
                    
                if($public_comboCheck == true)
                {
                    $public_combo = Yii::$app->getSecurity()->generateRandomString(10);
                }
                
                $target_file = $_FILES["sharex"]["name"];
                $fileType = pathinfo($target_file, PATHINFO_EXTENSION);
                $fileName = pathinfo($target_file, PATHINFO_FILENAME);
                
                $sharexdir = Url::to('@files/');; //Protected file directory
                
                $valid_file_extensions = array("txt", "png", "jpeg", "jpg", "gif", "mp4");
                
                if (in_array($fileType, $valid_file_extensions))
                {
                    //Accepts and moves to directory
                    if (move_uploaded_file($_FILES["sharex"]["tmp_name"], $sharexdir . $combo . '.' . $fileType))
                    {
                        $fileSize = filesize( Url::to('@files/' . $combo . '.' . $fileType) ); // bytes
                        //$fileSize = round($fileSize / 1024, 2);
    
                        //Create db entry
                        $newFile = new FileEntry;
                        $newFile->combo = $combo;
                        $newFile->public_combo = $public_combo;
                        $newFile->name = $fileName;
                        $newFile->user = $_POST['id'];
                        $newFile->type = $fileType;
                        $newFile->size = $fileSize;
                        $newFile->save();
    
                        //Sends info to client
                        $json->status = 200;
                        $json->public_combo = $public_combo;
                        $json->name = $fileName;
                                            
                        Yii::$app->user->logout();
                        
                    }
                        else
                    {
                        //Warning
                       echo 'File Upload Failed :: Please contact a server administrator.';
                    }
                }
                else
                {
                    echo 'File Upload Failed :: That extension is not allowed.';                
                }
            }
            else
            {
                //Invalid key
                echo 'Invalid ID / Secret Combination :: Please login and try again.';
            }
        }
        else
        {
            //Warning if no uploaded data
            echo 'No ID / Secret :: Please login and try again.';
        }
        //Sends json
        if(isset($json))
        {	
			return Url::to('view/', true) . $json->public_combo;
        }
    }
    
    public function actionViewFile()
    {
        //If not owner or on auth user list (auth_id_list), reroute to denied

        $request = Yii::$app->request;
        
        $fileId = $request->get('id');
        
        if(isset($fileId))
        {
			
            $model = FileEntry::find()
                ->where(['public_combo' => $fileId])
                ->one();
                
            //check if its a private file, and if accesser is owner
            if(isset($model) && ($model->ispublic == 1 || (!Yii::$app->user->isGuest && $model->user == Yii::$app->user->identity->ms_id) || (in_array(Yii::$app->user->identity->id, array($model->auth_id_list)))))
			{             
                $session = Yii::$app->session;
                $viewed = $session->get('viewed_files');
                if(isset($viewed))
                {	
                    if(!in_array( $model->combo, $viewed))
                    {
                        //view file
                        $viewedfiles = $session->get('viewed_files');
                        $viewedfiles[] = $model->combo;
                        $session->set('viewed_files', $viewedfiles);
                        
                        //$check = $session->get('viewed_files');
                        //echo print_r($check);
                        
                        $model->views = $model->views + 1;
                        $model->save();
                    }
                    else
                    {
                        //file already viewed
                        $viewedfiles = $session->get('viewed_files');
                        //$check = $session->get('viewed_files');
                        //echo print_r($check);
                    }
                }
                else
                {
                    //view file
                    $session->set('viewed_files', array ( $model->combo ));
                    $viewedfiles = $session->get('viewed_files');
                    
                    $model->views = $model->views + 1;
                    $model->save();
                }

				return $this->render('view-file', [
					'model' => $model,                
				]);
			}
            else
			{
                if(isset($model) && $model->ispublic == 0)
                {
                    $msg = Yii::$app->session->setFlash('error', 'The file you requested is marked as private.');
                }
                else
                {
                    $msg = Yii::$app->session->setFlash('error', 'The file you requested does not exist.');
                }
                
                $msg;
                
				return $this->goHome();
			}  
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionViewRaw()
    {
        $request = Yii::$app->request;
        
        $fileId = $request->get('id');
        
        if(isset($fileId))
        {
            $model = FileEntry::find()
                ->where(['public_combo' => $fileId])
                ->one();

            if(isset($model) && Yii::$app->controller->action->id !== 'library' && ($model->ispublic == 1 || (!Yii::$app->user->isGuest && $model->user == Yii::$app->user->identity->ms_id) || (in_array(Yii::$app->user->identity->id, array($model->auth_id_list)))))
			{
                $session = Yii::$app->session;
                $viewed = $session->get('viewed_files');
                if(isset($viewed))
                {	
                    if(!in_array( $model->combo, $viewed))
                    {
                        //view file
                        $viewedfiles = $session->get('viewed_files');
                        $viewedfiles[] = $model->combo;
                        $session->set('viewed_files', $viewedfiles);
                        
                        //$check = $session->get('viewed_files');
                        //echo print_r($check);
                        
                        $model->views = $model->views + 1;
                        $model->save();
                    }
                    else
                    {
                        //file already viewed
                        $viewedfiles = $session->get('viewed_files');
                        //$check = $session->get('viewed_files');
                        //echo print_r($check);
                    }
                }
                else
                {
                    //view file
                    $session->set('viewed_files', array ( $model->combo ));
                    $viewedfiles = $session->get('viewed_files');
                    
                    $model->views = $model->views + 1;
                    $model->save();
                }
                
                if($model->type == 'txt')
                {
                    $fn = fopen( Url::to('@files/' . $model->combo . '.' . $model->type) ,"r");
                    $numRow = 0;
                    
                    while(!feof($fn))  {
                        $result = fgets($fn);
                        echo ("
                                <td style='font-size: 13px; border: 0; font-family: Courier New; padding: 0px; padding-left: 8px;'>" . $result . "</td>
                                <br>
                            ");
                    }
                    fclose($fn);
                }
				elseif($model->type == 'mp4')
				{
					echo ("
							<div align='center' class='embed-responsive embed-responsive-16by9'>
							  <video class='' controls autoplay>
								  <source src='" . Url::toRoute(['file/load-image/', 'id' => $model->public_combo]) . "' type='video/mp4'>
							  </video>
							</div>
						  ");								
				}
                else
                {
                    echo ("
                          <img src='". Url::toRoute(['file/load-image/', 'id' => $model->public_combo]) . "'>
                          ");
                }
            }
            else
            {
                if(isset($model) && $model->ispublic == 0)
                {
                    $msg = Yii::$app->session->setFlash('error', 'The file you requested is marked as private.');
                }
                else
                {
                    $msg = Yii::$app->session->setFlash('error', 'The file you requested does not exist.');
                }
                
                $msg;
                
                return $this->goHome();
			}
        }
    }
    
    public function actionViewDownload()
    {
        $request = Yii::$app->request;
        
        $fileId = $request->get('id');
        
        if(isset($fileId))
        {     
            $model = FileEntry::find()
                ->where(['public_combo' => $fileId])
                ->one();
                
            if(isset($model) && ($model->ispublic == 1 || (!Yii::$app->user->isGuest && $model->user == Yii::$app->user->identity->ms_id) || (in_array(Yii::$app->user->identity->id, array($model->auth_id_list)))))
            {
                $session = Yii::$app->session;
                $viewed = $session->get('viewed_files');
                if(isset($viewed))
                {	
                    if(!in_array( $model->combo, $viewed))
                    {
                        //view file
                        $viewedfiles = $session->get('viewed_files');
                        $viewedfiles[] = $model->combo;
                        $session->set('viewed_files', $viewedfiles);
                        
                        //$check = $session->get('viewed_files');
                        //echo print_r($check);
                        
                        $model->views = $model->views + 1;
                        $model->downloads = $model->downloads + 1;
                        $model->save();
                    }
                    else
                    {
                        //file already viewed
                        $viewedfiles = $session->get('viewed_files');
                        //$check = $session->get('viewed_files');
                        //echo print_r($check);
                    }
                }
                else
                {
                    //view file
                    $session->set('viewed_files', array ( $model->combo ));
                    $viewedfiles = $session->get('viewed_files');
                    
                    $model->views = $model->views + 1;
                    $model->downloads = $model->downloads + 1;
                    $model->save();
                }
                
                $file = Url::to('@files/' . $model->combo . '.' . $model->type);
                
                $fileName = basename($file);
                
                header("Content-Disposition: attachment; filename=" . basename($file) . "");
                header("Content-Length: " . filesize($file));
                header("Content-Type: application/octet-stream;");
                readfile($file);             
            }
            else
            {
                if(isset($model) && $model->ispublic == 0)
                {
                    $msg = Yii::$app->session->setFlash('error', 'The file you requested is marked as private.');
                }
                else
                {
                    $msg = Yii::$app->session->setFlash('error', 'The file you requested does not exist.');
                }
                
                $msg;
                
				return $this->goHome();
			}
        }
    }
    
    public function actionDeleteFile()
    {
        if(Yii::$app->request->isAjax)
        {
            $data = Yii::$app->request->post();
            //data: { 'save_id' : fileid },
            $keys =  $data['keys'];
            // your logic;
            foreach ($keys as $key) {
                $find = FileEntry::find()
                ->where(['id' => $key])
                ->one();
                
                if($find->user == Yii::$app->user->identity->ms_id)
                {
                    $getFile = Url::to('@files/' . $find->combo . '.' . $find->type);

                    $valid_file_extensions = array("png", "jpeg", "jpg", "gif");

                    if (in_array($find->type, $valid_file_extensions))
                    {
                        $thumb = Yii::$app->thumbnail->url($getFile, ['thumbnail' => ['width' => 64, 'height' => 64]]);
                        
                        if(isset($thumb))
                        {
                            $link = Url::to('@webroot') . $thumb;
                            unlink($link);
                        }                        
                    }
                    
                    unlink($getFile);
                    
                    $find->delete();
                    
                    Yii::$app->session->setFlash('success', 'Your request has been successful.');                    
                }
                else
                {
                    Yii::$app->session->setFlash('error', "Your request could not be completed.");
                }
            }
            unset($data);
            unset($keys);
            
        }
    }
    
    public function actionEditFileName()
    {
        if(Yii::$app->request->isAjax)
        {
            if (Yii::$app->request->post('editableKey')) {

                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                
                $values = current($_POST['FileEntry']);
                $name = $values['name'];
                
                $data = Yii::$app->request->post();
                $id = $data['editableKey'];
                
                $find = FileEntry::find()
                    ->where(['id' => $id])
                    ->one();
                    
                $countName = strlen($name);
                
                if($countName <= 30)
                {
                    if($find->user == Yii::$app->user->identity->ms_id)
                    {
                        $find->name = $name;
                        $find->save();
            
                        return \yii\helpers\Json::encode(['output' => '', 'message' => '']);
                    }
                }
                else
                {
                    return \yii\helpers\Json::encode(['output' => 'error', 'message' => $countName]);
                }
            }
        }
    }
    
    public function actionTogglePublic()
    {
        if(Yii::$app->request->isAjax)
        {
            if (Yii::$app->request->post('id')) {

                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                
                $data = Yii::$app->request->post();
                $id = $data['id'];
                $status = $data['status'];
                $ispublic = (int) filter_var($status, FILTER_VALIDATE_BOOLEAN);
                
                $find = FileEntry::find()
                    ->where(['id' => $id])
                    ->one();
                
                if($find->user == Yii::$app->user->identity->ms_id)
                {
                    $find->ispublic = $ispublic;
                    $find->save();

                    return \yii\helpers\Json::encode(['output' => '', 'message' => 'success ' . $ispublic . ' ' . $id]);
                }
            }
        }
    }
        
    public function actionResetFileId()
    {
        if(Yii::$app->request->isAjax)
        {
            if(Yii::$app->request->post('id'))
            {
                $id = Yii::$app->request->post('id');
                
                $model = FileEntry::find()
                    ->where(['public_combo' => $id])
                    ->one();
                    
                if($model->user == Yii::$app->user->identity->ms_id)
                {
                    $newPubId = Yii::$app->getSecurity()->generateRandomString(10);
                    
                    $pubIdCheck = FileEntry::find()
                        ->where(['public_combo' => $newPubId])
                        ->exists();
                        
                    if($pubIdCheck == false)
                    {
                        $newPubId = Yii::$app->getSecurity()->generateRandomString(10);
                        
                        $model->public_combo = $newPubId;
                        $model->save();
                        
                        Yii::$app->session->setFlash('success', 'Your request has been successful.');
                    }
                }
            }
        }
    }
    
    public function actionLoadImage()
    {
        if(Yii::$app->request->isGet)
        {
            if(Yii::$app->request->get('id'))
            {
                $id = Yii::$app->request->get('id');
                
                $model = FileEntry::find()
                    ->where(['public_combo' => $id])
                    ->one();
                    
                if(isset($model) && ($model->ispublic == 1 || (!Yii::$app->user->isGuest && $model->user == Yii::$app->user->identity->ms_id) || (in_array(Yii::$app->user->identity->id, array($model->auth_id_list)))))
                {
                    
                    $session = Yii::$app->session;
                    $viewed = $session->get('viewed_files');
                    if(isset($viewed))
                    {	
                        if(!in_array($model->combo, $viewed) && $model->user !== Yii::$app->user->identity->ms_id)
                        {
                            //view file
                            $viewedfiles = $session->get('viewed_files');
                            $viewedfiles[] = $model->combo;
                            $session->set('viewed_files', $viewedfiles);
                            
                            //$check = $session->get('viewed_files');
                            //echo print_r($check);
                            
                            $model->views = $model->views + 1;
                            $model->save();
                        }
                        else
                        {
                            //file already viewed
                            $viewedfiles = $session->get('viewed_files');
                            //$check = $session->get('viewed_files');
                            //echo print_r($check);
                        }
                    }
                    else
                    {
                        //view file
                        $session->set('viewed_files', array ( $model->combo ));
                        $viewedfiles = $session->get('viewed_files');
                        
                        $model->views = $model->views + 1;
                        $model->save();
                    }
                    
                    if(!Yii::$app->user->isGuest)
                    {
                        $text = Yii::$app->user->identity->ms_id . ' ' . Yii::$app->getRequest()->getUserIP();
                    }
                    else
                    {
                        $text = Yii::$app->getRequest()->getUserIP();
                    }
					
                    if($model->type !== 'mp4')
					{
						Image::text(Url::to('@files/' . $model->combo . '.' . $model->type), $text, Url::to('@webroot/assets/siteassets/monaco.ttf'), [0, 0], ['size' => '6', 'color' => 'fff'])
							->save(Url::to('@files/' . $model->public_combo . '.' . $model->type));
						
						$readCombo = $model->public_combo;
						
						header('Content-Type: image/' . $model->type . '');
                        readfile(Url::to('@files/' . $readCombo . '.' . $model->type));
					}
					else
					{
						$readCombo = $model->combo;
						
						header('Content-Type: video/' . $model->type . '');
                        readfile(Url::to('@files/' . $readCombo . '.' . $model->type));
					}
                    
                    unlink(Url::to('@files/' . $model->public_combo . '.' . $model->type));
                }
                else
                {
                    Yii::$app->session->setFlash('error', 'The file you requested is marked as private.');
                    return $this->goHome();                    
                }
                
            }
        }
    }
    
    public function actionAddSecondaryUser()
    {
        if(Yii::$app->request->isAjax)
        {
            if(Yii::$app->request->post('id'))
            {
                    $id = Yii::$app->request->post('id');
                        
                    $file = Yii::$app->request->post('file');
                    
                    $file = FileEntry::find()
                        ->where(['id' => $file])
                        ->one();
                        
                    if($file->user == Yii::$app->user->identity->ms_id)
                    {
                        $encoded = json_encode($id, JSON_NUMERIC_CHECK);
                        
                        $trim = trim($encoded, '[]');
                        
                        $file->auth_id_list = $trim;
                        
                        $file->save();
                        
                        unset($file);
                        unset($id);
                    }
            }
            elseif(Yii::$app->request->post('file'))
            {
                $file = Yii::$app->request->post('file');
                
                $file = FileEntry::find()
                    ->where(['id' => $file])
                    ->one();
                                                
                $file->auth_id_list = "";
                
                $file->save();              
            }
        }        
    }
}
