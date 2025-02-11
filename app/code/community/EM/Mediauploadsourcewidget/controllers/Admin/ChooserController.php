<?php
class EM_Mediauploadsourcewidget_Admin_ChooserController extends Mage_Adminhtml_Controller_Action
{
    public function chooserAction()
    {
	    $uniqId = $this->getRequest()->getParam('uniq_id');
        $pagesGrid = $this->getLayout()->createBlock('mediauploadsourcewidget/media', '', array(
            'id' => $uniqId,
        ));
    	$html =$this->getLayout()->createBlock('mediauploadsourcewidget/content')->toHtml();
        $media =$pagesGrid->toHtml();
        $string ='<input type="hidden" value='.$uniqId.' id="uniqID"/>' ;
        $this->getResponse()->setBody($html.$string.'<div id="contents">'.$media.'</div>');
    	
    }
    public function contentsAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $pagesGrid = $this->getLayout()->createBlock('mediauploadsourcewidget/media', '', array(
            'id' => $uniqId,
        ));
        $media =$pagesGrid->toHtml();
        $this->getResponse()->setBody($media);
    }
    public function uploadAction()
    {
        $result = array();
        try {
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('mp3','flv','swf','avi','wmv'));
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save(
                Mage::getBaseDir('media') . DS . 'flash'
            );
            $result['url'] = $this->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'];
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }
        $abc = Mage::helper('core')->jsonEncode($result);
         $this->getResponse()->setBody($abc);
   	 }
     private function getBaseTmpMediaUrl()
    {
        return Mage::getBaseUrl('media') . 'flash';
    }
    private function getTmpMediaUrl($file)
    {
        $file = $this->_prepareFileForUrl($file);

        if(substr($file, 0, 1) == '/') {
            return $this->getBaseTmpMediaUrl() . $file;
        }
        return $this->getBaseTmpMediaUrl() . '/' . $file;
    }
    private function _prepareFileForUrl($file)
    {
        return str_replace(DS, '/', $file);
    }
}