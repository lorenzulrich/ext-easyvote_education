<?php
namespace Visol\EasyvoteEducation\Domain\Repository;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * The repository for PanelInvitations
 */
class PanelInvitationRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	protected $defaultOrderings = array(
		'panel.date' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
	);

	public function findNotIgnoredPanelsByCommunityUser(\Visol\Easyvote\Domain\Model\CommunityUser $communityUser) {
		if ($communityUser->getCitySelection() instanceof \Visol\Easyvote\Domain\Model\City && is_object($communityUser->getParty())) {
			$query = $this->createQuery();
			$query->matching(
				$query->logicalAnd(
					$query->greaterThanOrEqual('panel.date', time() - 86400),
					$query->contains('allowedParties', $communityUser->getParty()),
					$query->logicalNot(
						$query->contains('ignoringCommunityUsers', $communityUser)
					),
					$query->logicalOr(
						$query->equals('attendingCommunityUser', $communityUser),
						$query->equals('attendingCommunityUser', 0)
					),
					$query->equals('panel.city.kanton', $communityUser->getCitySelection()->getKanton())
				)
			);
			return $query->execute();

		} else {
			return NULL;
		}

	}
	
}