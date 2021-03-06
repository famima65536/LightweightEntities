<?php

namespace famima65536\lwe\entity;

use famima65536\lwe\entity\utils\policy\ZombieSearchEntityPolicy;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class LightweightZombie extends LightweightUndead {

	protected ?string $ambientSound = "mob.zombie.say";

	public function __construct(Location $location, ?CompoundTag $nbt = null){
		parent::__construct($location, $nbt);
		$this->searchPolicy = ZombieSearchEntityPolicy::getInstance();
	}

	public static function getNetworkTypeId() : string{ return EntityIds::ZOMBIE; }

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.8, 0.6); //TODO: eye height ??
	}

	public function getName() : string{
		return "Zombie";
	}

	public function getDrops() : array{
		$drops = [
			VanillaItems::ROTTEN_FLESH()->setCount(mt_rand(0, 2))
		];

		if(mt_rand(0, 199) < 5){
			$drops[] = match (mt_rand(0, 2)) {
				0 => VanillaItems::IRON_INGOT(),
				1 => VanillaItems::CARROT(),
				2 => VanillaItems::POTATO(),
			};
		}

		return $drops;
	}

	public function getXpDropAmount() : int{
		//TODO: check for equipment and whether it's a baby
		return 5;
	}

	public function entityBaseTick(int $tickDiff = 1): bool{
		$this->attackTicker($tickDiff);
		return parent::entityBaseTick($tickDiff); // TODO: Change the autogenerated stub
	}

	public function actionAttack(Entity $target): void{
		$this->actionAttackTime = 40;
		$ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 1);
		$target->attack($ev);
		$this->broadcastAnimation(new ArmSwingAnimation($this));
	}

	public function onTargetSelect(?Entity $target): void{
		$this->setTargetEntity($target);
	}
}