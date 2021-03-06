<?php

namespace famima65536\lwe\entity;

use famima65536\lwe\entity\utils\ChaseTargetTrait;
use famima65536\lwe\entity\utils\policy\SearchEntityPolicy;
use famima65536\lwe\entity\utils\state\WaitingState;
use famima65536\lwe\entity\utils\SearchTargetTrait;
use pocketmine\block\Transparent;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;

abstract class LightweightLiving extends Living {

	use ChaseTargetTrait, SearchTargetTrait;

	protected float $boundingRadius = 0.5;
	protected $jumpVelocity = 0.5;

	protected StateManager $stateManager;

	protected ?string $ambientSound = null;
	protected int $soundTick = 0;

	public function __construct(Location $location, ?CompoundTag $nbt = null){
		parent::__construct($location, $nbt);
		$this->stateManager = new StateManager(new WaitingState);
		$this->moveSpeedAttr->setValue(0.3);
		$this->setNameTagAlwaysVisible(true);
		$this->setNameTag((string) $this->getId());
	}

	public function spawnTo(Player $player): void{
		parent::spawnTo($player); // TODO: Change the autogenerated stub
	}

	public function onUpdate(int $currentTick): bool{
		if($this->ambientSound !== null && $this->soundTick === 0){
			$this->soundTick = 200 + mt_rand(0,20);
			$this->playIdleSound();
		}
		$nearest = $this->getNearestEntityMatchPolicy(1, SearchEntityPolicy::getInstance());
		if($nearest !== null){
			$boundingMotion = $nearest->getPosition()->subtractVector($this->location)->normalize()->multiply(-1);
			$this->addMotion($boundingMotion->x, 0, $boundingMotion->z);
		}
		return parent::onUpdate($currentTick); // TODO: Change the autogenerated stub
	}

	public function entityBaseTick(int $tickDiff = 1): bool{
		$this->stateManager->ticker($tickDiff);

		if($this->soundTick > 0){
			$this->soundTick -= $tickDiff;
			if($this->soundTick < 0){
				$this->soundTick = 0;
			}
		}

		if($this->searchTargetTime > 0){
			$this->searchTargetTime -= $tickDiff;
			if($this->searchTargetTime < 0){
				$this->searchTargetTime = 0;
			}
		}
		if($this->stateManager->getState()->isFinished()){
			$this->onStateFinished();
		}
		return parent::entityBaseTick($tickDiff); // TODO: Change the autogenerated stub
	}

	public function playIdleSound(): void{
		$this->getWorld()->broadcastPacketToViewers($this->location, PlaySoundPacket::create($this->ambientSound, $this->location->x, $this->location->y, $this->location->z, 100, 1+mt_rand(1,100)/100));
	}

	public function moveStraight(): void{
		$vectorPlane = $this->getDirectionPlane();
		$normalizedVector = new Vector3($vectorPlane->x, 0, $vectorPlane->y);
		if(!$this->getWorld()->getBlock($this->location->addVector($normalizedVector)) instanceof Transparent){
			$this->jump();
		}else{
			$motion = $normalizedVector->multiply($this->getMovementSpeed());
			if(!$this->isOnGround()){
				$motion = $motion->multiply(0.5);
			}
			$this->lastMotion->x = $motion->x;
			$this->lastMotion->z = $motion->z;
			$this->motion = clone $this->lastMotion;
		}

	}

	protected function onStateFinished(): void{
	}



}