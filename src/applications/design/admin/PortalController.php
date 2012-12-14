<?php
Wind::import('ADMIN:library.AdminBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PortalController.php 19070 2012-10-10 08:19:50Z gao.wanggao $ 
 * @package 
 */

class PortalController extends AdminBaseController {
	
	public function run() {
		$page = (int)$this->getInput('page','get');
		$perpage = 10;
		$args = array();
		$page =  $page > 1 ? $page : 1;
		list($start, $perpage) = Pw::page2limit($page, $perpage);
		Wind::import('SRV:design.srv.vo.PwDesignPortalSo');
		$vo = new PwDesignPortalSo();
		$ds = $this->_getPortalDs();
		$count = $ds->countPartal($vo);
		$list = $ds->searchPortal($vo, $start, $perpage);
		$pageList = $this->_getPageDs()->fetchPageByTypeUnique(PwDesignPage::PORTAL, array_keys($list));
		foreach ($pageList AS $k=>$v) {
			foreach ($list AS $_k=>$_v) {
				if ($v['page_unique'] == $_k) $list[$_k]['page_id']	= $k;
			}
		}
		$this->setOutput($list ,'list');
		$this->setOutput($count, 'count');
		$this->setOutput($page, 'page');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput(ceil($count/$perpage), 'totalpage');
		$this->setOutput('design/portal/run', 'pageurl');
	}
	
	public function deleteAction() {
		$portalid = (int)$this->getInput('id','get');
		//TODO 删除数据
		$pageInfo = $this->_getPageDs()->getPageByTypeAndUnique(PwDesignPage::PORTAL,$portalid);
		if ($pageInfo) {
			$ids = explode(',', $pageInfo['module_ids']);
			$names = explode(',', $pageInfo['module_names']);
			$moduleDs = $this->_getModuleDs();
			$bakDs = $this->_getBakDs();
			$dataDs = $this->_getDataDs();
			$pushDs = $this->_getPushDs();
			$imageSrv = Wekit::load('design.srv.PwDesignImage');
			$moduleDs->deleteByPageId($pageInfo['page_id']);
			// module&& data && push
			foreach ($ids AS $id) {
				$dataDs->deleteByModuleId($id);
				$pushDs->deleteByModuleId($id);
				$imageSrv->clearFolder($id);
			}
			
			//structure
			$ds = $this->_getStructureDs();
			foreach ($names AS $name) {
				$ds->deleteStruct($name);
			}
			
			//segment
			$this->_getSegmentDs()->deleteSegmentByPageid($pageInfo['page_id']);
			$this->_getPageDs()->deletePage($pageInfo['page_id']);
			$this->_getPermissionsDs()->deleteByTypeAndDesignId(PwDesignPermissions::TYPE_PAGE, $pageInfo['page_id']);
		}
		$this->_getDesignService()->clearTemplate($pageInfo['page_id']);
		if ($this->_getPortalDs()->deletePortal($portalid)) $this->showMessage("operate.success");
		$this->showMessage("operate.fail");
	}
	
	public function batchopenAction() {
		$ids = $this->getInput('ids','post');
		$isopen = $this->getInput('isopen','post');
		$ds = $this->_getPortalDs();
		foreach ($ids AS $id) {
			$ds->updatePortalOpen($id, $isopen[$id]);
		}
		$this->showMessage("operate.success");

	}
	
	/*
	public function batchdeleteAction() {
		$ids = (int)$this->getInput('ids','post');
		if ($this->_getPortalDs()->batchDelete($ids)) $this->showMessage("operate.success");
		$this->showMessage("operate.fail");
	}*/
	
	private function _getDesignService() {
		return Wekit::load('design.srv.PwDesignService');
	}
	
	private function _getPermissionsDs() {
		return Wekit::load('design.PwDesignPermissions');
	}
	
	private function _getStructureDs() {
		return Wekit::load('design.PwDesignStructure');
	}
	
	private function _getModuleDs() {
		return Wekit::load('design.PwDesignModule');
	}
	
	
	private function _getBakDs() {
		return Wekit::load('design.PwDesignBak');
	}
	
	private function _getSegmentDs() {
		return Wekit::load('design.PwDesignSegment');
	}
	
	private function _getDataDs() {
		return Wekit::load('design.PwDesignData');
	}
	
	private function _getPushDs() {
		return Wekit::load('design.PwDesignPush');
	}
	
	
	private function _getPortalDs() {
		return Wekit::load('design.PwDesignPortal');
	}
	
	private function _getPageDs() {
		return Wekit::load('design.PwDesignPage');
	}
}