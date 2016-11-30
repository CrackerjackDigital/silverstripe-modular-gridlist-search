<?php
namespace Modular\GridList\Providers\Items;

use Modular\Constraints\Search as Constraint;
use Modular\Services\Search as SearchService;
use Modular\Fields\ModelTag;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\Models\Tag;

/**
 * Provides items which match 'tags' query paramter as ModelTags - tags can be a csv list of ModelTags.
 *
 * @package Modular\Search
 */
class SearchTags extends \Modular\Extensions\Model\Search implements ItemsProvider {

	public function provideGridListItems() {
		$results = new \ArrayList();

		/** @var SearchService $service */
		$service = \Injector::inst()->get('SearchService');

		if ($tags = array_filter(explode(',', $service->constraint(Constraint::TagsVar)))) {
			$allTags = Tag::get();
			$searchClasses = $this->search_classes();

			foreach ($tags as $tag) {
				/** @var ModelTag $tag */
				if ($tag = $allTags->find(ModelTag::single_field_name(), $tag)) {
					// e.g. merge all related classes that end in 'Page' for this tag
					// would be indicated by config.search_classes entry '*Page'
					$results->merge($tag->relatedByClassName($searchClasses));
				}
			}
		}
		return $results;
	}
}