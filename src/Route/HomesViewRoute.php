<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560
 * @link https://github.com/ShockedPlot7560
 *
 *
 */

namespace ShockedPlot7560\FactionMaster\Route;

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionSlug;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\SimpleForm;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function is_string;

class HomesViewRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "homesViewRoute";

	public function getSlug(): string {
		return self::HOMES_VIEW_ROUTE;
	}

	public function getPermissions(): array {
		return [
			PermissionIds::PERMISSION_TP_FACTION_HOME
		];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::MAIN_ROUTE);
	}

	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);

		$this->setCollection(CollectionFactory::get(CollectionSlug::HOMES_VIEW_COLLECTION)->init($this->getPlayer(), $this->getUserEntity()));
		$homes = MainAPI::getFactionHomes($this->getUserEntity()->getFactionName());

		$message = "";
		if (isset($params[0]) && is_string($params[0])) {
			$message = $params[0];
		}
		if (count($homes) == 0) {
			$message .= Utils::getText($this->getUserEntity()->getName(), "NO_HOME_SET");
		}

		$player->sendForm($this->getForm($message));
	}

	public function call(): callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}
			$this->getCollection()->process($data, $player);
			return;
		};
	}

	protected function getForm(string $message = ""): SimpleForm {
		$menu = new SimpleForm($this->call());
		$menu = $this->getCollection()->generateButtons($menu, $this->getUserEntity()->getName());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "HOME_FACTION_PANEL_TITLE"));
		$content = Utils::getText($this->getUserEntity()->getName(), "HOME_FACTION_PANEL_CONTENT");
		if ($message !== "") {
			$content .= ("\n§r" . $message);
		}
		$menu->setContent($content);
		return $menu;
	}
}