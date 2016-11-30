<?php
namespace Modular\GridList\Providers\Items;

use Modular\Constraints\Search as Constraint;
use Modular\Services\Search as SearchService;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;

/**
 * Provides items which match fulltext parameter 'q'
 *
 * @package Modular\Search
 */
class SearchFulltext extends \Modular\Extensions\Model\Search implements ItemsProvider {

	public function provideGridListItems() {
		$results = new \ArrayList();

		/** @var SearchService $service */
		$service = \Injector::inst()->get('SearchService');

		// check something was passed in 'q' parameter up front to skip processing if we can
		if ($service->constraint(Constraint::FullTextVar)) {
			$searchClasses = $this->search_classes();

			foreach ($searchClasses as $className) {
				$filter = $service->Filters()->filter($className, Constraint::FullTextVar, $this->searchIndex());
				if ($filter) {
					$intermediates = \DataObject::get($className)
						->filter($filter);

					/** @var ModelExtension|\DataObject $intermediate */
					foreach ($intermediates as $intermediate) {
						if ($intermediate->hasMethod('SearchTargets')) {

							// merge in what the intermediate object thinks are it's actual targets,
							// e.g. for a ContentBlock this is the Pages which are related to that block
							$results->merge($intermediate->SearchTargets());

						} else {
							// if no search targets nominated then just add the intermediate as it is the target
							$results->push($intermediate);
						}
					}
				}
			}
		}
		return $results;
	}
}