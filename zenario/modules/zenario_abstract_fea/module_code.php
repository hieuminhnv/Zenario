<?php
/*
 * Copyright (c) 2016, Tribal Limited
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of Zenario, Tribal Limited nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL TRIBAL LTD BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
if (!defined('NOT_ACCESSED_DIRECTLY')) exit('This file may not be directly accessed');



class zenario_abstract_fea extends module_base_class {

	public function init() {
		//if (in(request('method_call'), 'fillVisitorTUIX', 'formatVisitorTUIX', 'validateVisitorTUIX', 'saveVisitorTUIX')) {
		//} else {
		//}
		
		return true;
	}

	public function showSlot() {
		//$this->twigFramework(array());
	}
	
	
	
	
	
	protected function populateItemsIdCol($path, $customisationName, &$tags, &$fields, &$values) {
		return 'id';
	}
	protected function populateItemsSelect($path, $customisationName, &$tags, &$fields, &$values) {
		return "SELECT id, name";
	}
	protected function populateItemsFrom($path, $customisationName, &$tags, &$fields, &$values) {
		return "FROM ". DB_NAME_PREFIX. "table";
	}
	protected function populateItemsWhere($path, $customisationName, &$tags, &$fields, &$values) {
		return "WHERE false";
	}
	protected function populateItemsOrderBy($path, $customisationName, &$tags, &$fields, &$values) {
		return "ORDER BY name";
	}
	protected function populateItemsPageSize($path, $customisationName, &$tags, &$fields, &$values) {
		return false;
	}
	protected function formatItemRow(&$item, $path, &$tags, &$fields, &$values) {
		//...
	}
	
	protected function populateItems($path, $customisationName, &$tags, &$fields, &$values) {
		
		$page = 1;
		$limit = '';
		$itemCount = 0;
		$idCol =  $this->populateItemsIdCol($path, $customisationName, $tags, $fields, $values);
		
		if ($pageSize = $this->populateItemsPageSize($path, $customisationName, $tags, $fields, $values)) {
			$row = sqlFetchRow(
				"SELECT COUNT(*)
				". $this->populateItemsFrom($path, $customisationName, $tags, $fields, $values). "
				". $this->populateItemsWhere($path, $customisationName, $tags, $fields, $values));
			$itemCount = $row[0];
			
			if ((!$page = (int) request('page'))
			 || ($page > ceil($itemCount / $pageSize))) {
				$page = 1;
			}
			
			$limit = paginationLimit($page, $pageSize);
			$tags['__page_size__'] = $pageSize;
			$tags['__page__'] = $page;
		}
		
		
		$result = sqlSelect(
				$this->populateItemsSelect($path, $customisationName, $tags, $fields, $values). "
				". $this->populateItemsFrom($path, $customisationName, $tags, $fields, $values). "
				". $this->populateItemsWhere($path, $customisationName, $tags, $fields, $values). "
				". $this->populateItemsOrderBy($path, $customisationName, $tags, $fields, $values). "
				". $limit);
		
		$tags['items'] = array();
		$tags['__item_sort_order__'] = array();
		while ($item = sqlFetchAssoc($result)) {
			$this->formatItemRow($item, $path, $tags, $fields, $values);
			
			$tags['items'][$item[$idCol]] = $item;
			$tags['__item_sort_order__'][] = $item[$idCol];
			
			if (!$pageSize) {
				++$itemCount;
			}
		}
		$tags['__item_count__'] = $itemCount;
	}
}