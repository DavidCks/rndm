<?php
define("END_OF_FILE", 39393939);

class PNGNumberStrip
{
	public $im;
	public $w;
	public $h;
	public $imageByX;
	public $imageByY;
	public $numberStripDimensions;
	public $pixelCountArray;
	public $height;
	public $width;
	public $pixelCountArrayInPercent;
	public $white;
	public $BlackWhiteChanges;
	
	public function __construct($imgPNG)
	{
		$this->im = imagecreatefrompng("$imgPNG");
		$this->w = ImageSX($this->im);
		$this->h = ImageSY($this->im);
		$this->white = 1;
		$max = 0;
		for($x = 0; $x < $this->w; $x++)
		{
			for($y = 0; $y < $this->h; $y++)
			{
				$this->imageByX[$x][$y] = imagecolorat($this->im,$x,$y);
				$this->imageByY[$y][$x] = imagecolorat($this->im,$x,$y);
				$currentColor = $this->imageByX[$x][$y];
				if($currentColor > $max)
				{
					$max = $currentColor;
				}
			}
		}
		for($x = 0; $x < $this->w; $x++)
		{
			for($y = 0; $y < $this->h; $y++)
			{
				if($this->imageByX[$x][$y] > $max/10)
				{
					$this->imageByX[$x][$y] = $this->white;
				}
				else
				{
					$this->imageByX[$x][$y] = 0;
				}
				if($this->imageByY[$y][$x] > $max/10)
				{
					$this->imageByY[$y][$x] = $this->white;
				}
				else
				{
					$this->imageByY[$y][$x] = 0;
				}
			}
		}
		$this->numberStripDimensions = $this->getNumberStripDimensionsArray();
		$this->pixelCountArray = $this->pixelCount();
		for($i = 0; $i < count($this->pixelCountArray); $i++)
		{		
			for($l = 1; $l <= count($this->pixelCountArray[$i]); $l++)
			{
				if($this->pixelCountArray[$i][$l] == 0) 
				{
					$this->pixelCountArray[$i][$l] = 1;
				}
				$this->pixelCountArrayInPercent[$i][$l] = $this->pixelCountArray[$i][$l] / ($this->width[$i] * $this->height[$i]) * 100;
			}
		}
		$this->BlackWhiteChanges = $this->BWChanges();
	}
	
	public function analyze()
	{
		$index = 0;
		foreach($this->pixelCountArrayInPercent as $field)
		{
			//in wirklichkeit horizontal
			$vertical[$index][1] = $field[1] + $field[2] + $field[3];
			$vertical[$index][2] = $field[4] + $field[5] + $field[6];
			$vertical[$index][3] = $field[7] + $field[8] + $field[9];
			
			//in wirklichkeit vertikal
			$horizontal[$index][1] = $field[1] + $field[4] + $field[7];
			$horizontal[$index][2] = $field[2] + $field[5] + $field[8];
			$horizontal[$index][3] = $field[3] + $field[6] + $field[9];
			
			$index++;
		}	
		
		for($index = 0; $index < count($this->numberStripDimensions); $index++)
		{
			$maxBlackPixels = 0;
			foreach($this->pixelCountArray[$index] as $value)
			{
				$maxBlackPixels += $value;
			}
			
			$number[$index] = 12;
			$altNumber[$index] = 12;
			if($horizontal[$index][1] > $horizontal[$index][2] && $horizontal[$index][2] < $horizontal[$index][3]) //0,6,8,9,5(knapp dabei)
			{
				//5 (9)
				$sX = $this->numberStripDimensions[$index]['startX'];
				$sY = $this->numberStripDimensions[$index]['startY'];
				for($y = $sY + floor($this->height[$index]/2); $y < $sY + floor($this->height[$index]*(4/5)); $y++)
				{
					$noLine = 0;	
					for($x = $sX; $x < $sX + floor($this->width[$index]/2); $x++)
					{
						if($this->imageByX[$x][$y] == 0)
						{
							$noLine++;
						}
					}
					$noLine = $noLine/$maxBlackPixels;
					if($noLine < 1)
					{
						// (6)
						for($y2 = $sY + floor($this->height[$index]*(1/5)); $y2 < $sY + floor($this->height[$index]/3); $y2++)
						{
							$noLine2 = 0;
							for($x2 = $sX + floor($this->width[$index]/2); $x2 < $sX + $this->width[$index]; $x2++)
							{
								if($this->imageByX[$x2][$y2] == 0)
								{
									$noLine2++;
								}
							}
							$noLine2 = $noLine2/$maxBlackPixels;
							if($noLine2 < 1)
							{
								$number[$index] = 5;
								break;
							}
						}
					}
				}
				
				// 0,6,8,9
				// 9
				$sX = $this->numberStripDimensions[$index]['startX'];
				$sY = $this->numberStripDimensions[$index]['startY'];
				for($y = $sY + floor($this->height[$index]*(2/3)); $y < $sY + floor($this->height[$index]*(5/6)); $y++)
				{
					$noLine = 0;	
					for($x = $sX; $x < $sX + floor($this->width[$index]/2); $x++)
					{
						if($this->imageByX[$x][$y] == 0)
						{
							$noLine++;
						}
					}
					$noLine = $noLine/$maxBlackPixels;
					if($noLine < 1)
					{
						if($number[$index] != 5)
						{
							$number[$index] = 9;
						}
						else
						{
							$altNumber[$index] = 9;
						}
						break;
					}
				}
				// 8,6,0
				// 6
				$sX = $this->numberStripDimensions[$index]['startX'];
				$sY = $this->numberStripDimensions[$index]['startY'];
				for($y = $sY + floor(($this->height[$index])*(1/5)); $y < $sY + floor($this->height[$index]/2.5); $y++)
				{
					$noLine = 0;
					for($x = $sX + floor($this->width[$index]/2); $x < $sX + $this->width[$index]; $x++)
					{
						if($this->imageByX[$x][$y] == 0)
						{
							$noLine++;
						}
					}
					$noLine = $noLine/$maxBlackPixels;
					if($noLine < 1)
					{
						if($number[$index] != 9 && $number[$index] != 5)
						{
							$number[$index] = 6;
						}
						else
						{
							$altNumber[$index] = 6;
						}
						break;
					}
				}
				// 8,0	
				// 0
				$sX = $this->numberStripDimensions[$index]['startX'];
				$sY = $this->numberStripDimensions[$index]['startY'];
				$noLine = 0;
				for($y = $sY + floor($this->height[$index]*(2/5)); $y < $sY + floor($this->height[$index]*(3/5)); $y++)
				{
					for($x = $sX + floor($this->width[$index]*(2/5)); $x < $sX + floor($this->width[$index]*(3/5)); $x++)
					{
						if($this->imageByX[$x][$y] == 0)
						{
							$noLine++;
						}
					}					
				}
				$noLine = $noLine/$maxBlackPixels;
				if($noLine < 1)
				{
					if($number[$index] != 9 && $number[$index] != 6 && $number[$index] != 5)
					{
						$number[$index] = 0;
					}
					else
					{
						$altNumber[$index] = 0;
					}
				}
				if($number[$index] != 9 && $number[$index] != 6 && $number[$index] != 0 && $number[$index] != 5) //8
				{
					$number[$index] = 8;
				} 
				else
				{
					$altNumber[$index] = 8;
				}
			}
			if($horizontal[$index][1] < $horizontal[$index][2] && $horizontal[$index][2] < $horizontal[$index][3]) //1,2,3,4,5
			{
				//5 (9)
				$sX = $this->numberStripDimensions[$index]['startX'];
				$sY = $this->numberStripDimensions[$index]['startY'];
				for($y = $sY + floor($this->height[$index]/2); $y < $sY + floor($this->height[$index]*(4/5)); $y++)
				{
					$noLine = true;	
					for($x = $sX; $x < $sX + floor($this->width[$index]/2); $x++)
					{
						if($this->imageByX[$x][$y] == 0)
						{
							$noLine = false;
							break;
						}
					}
					if($noLine)
					{
						// (6)
						for($y2 = $sY + floor($this->height[$index]*(1/5)); $y2 < $sY + floor($this->height[$index]/2); $y2++)
						{
							$noLine2 = true;
							for($x2 = $sX + floor($this->width[$index]/2); $x2 < $sX + $this->width[$index]; $x2++)
							{
								if($this->imageByX[$x2][$y2] == 0)
								{
									$noLine2 = false;
								}
							}
							if($noLine2)
							{
								if($number[$index] != 9 && $number[$index] != 6 && $number[$index] != 0 && $number[$index] != 5) //8
								{
									$number[$index] = 5;
								}
								else
								{
									$altNumber[$index] = 5;
								}
								break;
							}
						}
					}
				}
				//1,2,3,4
					if($this->pixelCountArrayInPercent[$index][7] > 1)//2,3,4
					{
						// 3
						$sX = $this->numberStripDimensions[$index]['startX'];
						$sY = $this->numberStripDimensions[$index]['startY'];
						for($y = $sY + floor(($this->height[$index])/2); $y < $sY + floor($this->height[$index]*(3/4)); $y++)
						{
							$noLine = 0;
							for($x = $sX; $x < $sX + $this->width[$index]*(15/24); $x++)
							{
								if($this->imageByX[$x][$y] == 0)
								{
									$noLine++;
								}
							}
							$noLine = $noLine/$maxBlackPixels;
							if($noLine < 1)
							{
								if($number[$index] != 9 && $number[$index] != 6 && $number[$index] != 0 && $number[$index] != 5)
								{
									$number[$index] = 3;
								}
								else
								{
									$altNumber[$index] = 3;
								}
								break;
							}
						}
						// 4
						$sX = $this->numberStripDimensions[$index]['startX'];
						$sY = $this->numberStripDimensions[$index]['startY'];
						for($y = $sY + floor(($this->height[$index])/3); $y < $sY + floor($this->height[$index]*(3/4)); $y++)
						{
							$blackDetected = false;
							$whiteDetectedAfterwards = false;
							$blackDetectedAfterThat = false;
							for($x = $sX; $x < $sX + $this->width[$index]*(3/4); $x++)
							{
								if($this->imageByX[$x][$y] == 0)
								{
									$blackDetected = true;
								}
								if($blackDetected)
								{
									if($this->imageByX[$x][$y] == $this->white)
									{
										$whiteDetectedAfterwards = true;
									}
								}
								if($whiteDetectedAfterwards == true)
								{
									if($this->imageByX[$x][$y] == 0)
									{
										$blackDetectedAfterThat = true;
									}
								}
							}
							if($blackDetectedAfterThat == true)
							{
								if($number[$index] != 9 && $number[$index] != 6 && $number[$index] != 0 && $number[$index] != 5 && $number[$index] != 3)
								{	
									$number[$index] = 4;
								}
								else
								{
									$altNumber[$index] = 4;
								}
							}
						}
						if($number[$index] != 9 && $number[$index] != 6 && $number[$index] != 0 && $number[$index] != 5 && $number[$index] != 3 && $number[$index] != 4)
						{
							$number[$index] = 2;
						}
						else
						{
							$altNumber[$index] = 2;
						}
					}
					else //1,3,4
					{
						// 3
						$sX = $this->numberStripDimensions[$index]['startX'];
						$sY = $this->numberStripDimensions[$index]['startY'];
						for($y = $sY + floor(($this->height[$index])/2); $y < $sY + floor($this->height[$index]*(3/4)); $y++)
						{
							$noLine = true;
							for($x = $sX; $x < $sX + $this->width[$index]*(15/24); $x++)
							{
								if($this->imageByX[$x][$y] == 0)
								{
									$noLine = false;
								}
							}
							if($noLine == true)
							{
								$number[$index] = 3;
								break;
							}
						}
						if($number[$index] != 3)//1,4
						{
							// 4
							$sX = $this->numberStripDimensions[$index]['startX'];
							$sY = $this->numberStripDimensions[$index]['startY'];
							for($y = $sY + floor(($this->height[$index])/2); $y < $sY + floor($this->height[$index]*(3/4)); $y++)
							{
								$blackDetected = false;
								$whiteDetectedAfterwards = false;
								$blackDetectedAfterThat = false;
								for($x = $sX; $x < $sX + $this->width[$index]*(3/4); $x++)
								{
									if($this->imageByX[$x][$y] == 0)
									{
										$blackDetected = true;
									}
									if($blackDetected)
									{
										if($this->imageByX[$x][$y] == $this->white)
										{
											$whiteDetectedAfterwards = true;
										}
									}
									if($whiteDetectedAfterwards == true)
									{
										if($this->imageByX[$x][$y] == 0)
										{
											$blackDetectedAfterThat = true;
										}
									}
								}
								if($blackDetectedAfterThat == true)
								{
									$number[$index] = 4;
								}
							}
							if($number[$index] != 3 && $number[$index] != 4)//2,4
							{
								$number[$index] = 1;
							}
						}	
					}

			} else if($horizontal[$index][1] < $horizontal[$index][2] && $horizontal[$index][2] > $horizontal[$index][3]) //7,5
			{
				//5 (9)
				$sX = $this->numberStripDimensions[$index]['startX'];
				$sY = $this->numberStripDimensions[$index]['startY'];
				for($y = $sY + floor($this->height[$index]/2); $y < $sY + floor($this->height[$index]*(4/5)); $y++)
				{
					$noLine = true;	
					for($x = $sX; $x < $sX + floor($this->width[$index]/2); $x++)
					{
						if($this->imageByX[$x][$y] == 0)
						{
							$noLine = false;
							break;
						}
					}
					if($noLine)
					{
						// (6)
						for($y2 = $sY + floor($this->height[$index]*(1/5)); $y2 < $sY + floor($this->height[$index]/2); $y2++)
						{
							$noLine2 = true;
							for($x2 = $sX + floor($this->width[$index]/2); $x2 < $sX + $this->width[$index]; $x2++)
							{
								if($this->imageByX[$x2][$y2] == 0)
								{
									$noLine2 = false;
								}
							}
							if($noLine2)
							{
								$number[$index] = 5;
								break;
							}
						}
					}
				}
				if($number[$index] != 5)
				{
					$number[$index] = 7;
				}
			} else 
			{
				$number[$index] = 11;
			}
		}
		$guess[0] = $number;
		return $guess;
	}
	
	protected function isJailed($imageVert,$i,$s,$e)
	{
		$isJailed = true;
		for($y = $s; $y < $e; $y++)
		{
			if($imageVert[$i][0][$y] == 1)
			{
				$isJailed = false;
				break;
			}
			if($imageVert[$i][2][$y] == 1)
			{
				$isJailed = false;
				break;
			}
		}
		return $isJailed;
	}
	protected function isJailedLeft($imageVert,$i,$s,$e)
	{
		$isJailed = true;
		for($y = $s; $y < $e; $y++)
		{
			if($imageVert[$i][0][$y] == 1)
			{
				$isJailed = false;
				break;
			}
		}
		return $isJailed;
	}
	protected function isJailedRight($imageVert,$i,$s,$e)
	{
		$isJailed = true;
		for($y = $s; $y < $e; $y++)
		{
			if($imageVert[$i][2][$y] == 1)
			{
				$isJailed = false;
				break;
			}
		}
		return $isJailed;
	}
	
	public function analyze2()
	{
		$imageVert = array(array(array()));
		$imageHori = array(array(array()));
		$number = array();
		foreach($this->numberStripDimensions as $i => $dimensions)
		{
			$height = $this->height[$i];
			$width = $this->width[$i];
			//Vert
			for($y = $dimensions['startY']; $y < $dimensions['endY']; $y++)
			{
				//1
				$hasBlack1 = false; //init
				for($x = $dimensions['startX']; $x < round($width/3) + $dimensions['startX']; $x++)
				{
					if($this->imageByX[$x][$y] == 0)
					{
						$hasBlack1 = true;
						break;
					}
				}
				if($hasBlack1)
				{
					$imageVert[$i][0][$y - $dimensions['startY']] = 0;
				}
				else
				{
					$imageVert[$i][0][$y - $dimensions['startY']] = 1;
				}
				//2
				$hasBlack2 = false; //init
				for($x = $dimensions['startX'] + round($width/3); $x < round($width*(2/3)) + $dimensions['startX']; $x++)
				{
					if($this->imageByX[$x][$y] == 0)
					{
						$hasBlack2 = true;
						break;
					}
				}
				if($hasBlack2)
				{
					$imageVert[$i][1][$y - $dimensions['startY']] = 0;
				}
				else
				{
					$imageVert[$i][1][$y - $dimensions['startY']] = 1;
				}
				//3
				$hasBlack3 = false; //init
				for($x = $dimensions['startX'] + round($width*(2/3)); $x < $width + $dimensions['startX']; $x++)
				{
					if($this->imageByX[$x][$y] == 0)
					{
						$hasBlack3 = true;
						break;
					}
				}
				if($hasBlack3)
				{
					$imageVert[$i][2][$y - $dimensions['startY']] = 0;
				}
				else
				{
					$imageVert[$i][2][$y - $dimensions['startY']] = 1;
				}
			}
			
			$result = 0;
			$search = 0; //black
			for($y = 0; $y < $height; $y++)
			{
				if($search == 0)
				{
					if($imageVert[$i][1][$y] == $search)
					{
						$bwcPos[$result] = $y;
						$result++;
						$search = 1;
					}
				} 
				else if($search == 1 && $result > 0)
				{
					if($imageVert[$i][1][$y] == $search)
					{
						$bwcPos[$result] = $y;
						$result++;
						$search = 0;
					}
				}
			}
			//echo $result . "_$i<br>";
			$number[$i] = 11;
			//7 (9 or 2 by accident)
			if($result == 4)
			{
				$isJailedLeftTop = $this->isJailedLeft($imageVert,$i,$bwcPos[1],$bwcPos[2]);
				//9
				if($isJailedLeftTop)
				{
					$sY = round(($bwcPos[1] + $bwcPos[2])/2);
					$x = round($width/2);
					$isNine = false;
					for($y = $sY; $y < $height/2; $y++)
					{
						if($this->imageByX[$x][$y] == 0)
						{
							$isNine = true;
						}
					}
					if($isNine)
					{
						$number[$i] = 9;
					}
				}
				//7,2
				else
				{
					foreach($imageVert[$i] as $i => $val)
					{
						$y = round($height*(28/39));
						$hasBlackArr[$i] = 0;
						while($y < $height)
						{
							if($val[$y] == 0)
							{
								$hasBlackArr[$i] = 1;
							}	
							$y++;
						}
					}
					//2
					if($hasBlackArr[0] == 1 && $hasBlackArr[1] == 1 && $hasBlackArr[2] == 1)
					{
						$number[$i] = 2;
					}
					else
					{
						$number[$i] = 7;
					}
				}
			}
			//0 (2,7,8 or 9 by accident)
			else if($result == 3)
			{
				if($bwcPos[1] < $height/2 && $bwcPos[2] > $height*(2/3))
				{
					$isJailed = $this->isJailed($imageVert,$i,$bwcPos[1],$bwcPos[2]);
					if($isJailed)
					{
						$number[$i] = 0;
					}
				}
				//might be 2,7 or 8 by accident
				if($number[$i] != 0)
				{
					//7,8
					if($bwcPos[2] < $height*(2/3))
					{
						$isJailed = $this->isJailed($imageVert,$i,$bwcPos[1],$bwcPos[2]);
						if($isJailed)
						{
							$number[$i] = 8;
						}
						else
						{
							$number[$i] = 7;
						}
					}
					//2 or 9
					else
					{
						$blacksUpperHalf = 0;
						for($y = 0; $y < round($height/2); $y++)
						{
							if($imageVert[$i][1][$y] == 0)
							{
								$blacksUpperHalf++;
							}
						}
						$blackslowerHalf = 0;
						for($y = round($height/2); $y < $height; $y++)
						{
							if($imageVert[$i][1][$y] == 0)
							{
								$blackslowerHalf++;
							}
						}
						if($blacksUpperHalf > $blackslowerHalf)
						{
							$number[$i] = 9;
						}
						else
						{
							$number[$i] = 2;
						}
					}
				}
			}
			//2,3,5,6,8,9
			else if($result >= 5)
			{
				if($bwcPos[0] < $height/9)
				{
					$isJailedTop = $this->isJailed($imageVert,$i,$bwcPos[1],$bwcPos[2]);
					$isJailedBot = $this->isJailed($imageVert,$i,$bwcPos[3],$bwcPos[4]);
					if($isJailedTop && $isJailedBot)
					{
						$number[$i] = 8;
					}
					else if($isJailedTop && !$isJailedBot)
					{
						$number[$i] = 9;
					}
					//6 (2 by accident)
					else if(!$isJailedTop && $isJailedBot)
					{
						$isTwo = false;
						for($y = $height/2; $y < $height*(2/3); $y++)
						{
							if($imageVert[$i][0][$y] == 1)
							{
								$isTwo = true;
							}
						}
						if($isTwo)
						{
							$number[$i] = 2;
						}
						else
						{
							$number[$i] = 6;
						}
					}
					//2,3,5
					else if(!$isJailedTop && !$isJailedBot)
					{
						$isJailedLeftTop = $this->isJailedLeft($imageVert,$i,$bwcPos[1],$bwcPos[2]);
						$isJailedRightTop = $this->isJailedRight($imageVert,$i,$bwcPos[1],$bwcPos[2]);
						$isJailedLeftBot = $this->isJailedLeft($imageVert,$i,$bwcPos[3],$bwcPos[4]);
						$isJailedRightBot = $this->isJailedRight($imageVert,$i,$bwcPos[3],$bwcPos[4]);
						
						if($isJailedRightBot && !$isJailedRightTop)
						{
							$number[$i] = 5;
						}
						else if($isJailedRightBot && $isJailedRightTop)
						{
							$number[$i] = 3;
						}
						else if(!$isJailedLeftTop && !$isJailedRightBot)
						{
							$number[$i] = 2;
						}
					}
				}
			}
			//1,4
			else
			{
				//prep
				$resultX = 0;
				$search = 0; //black
				for($y = 0; $y < $height; $y++)
				{
					if($search == 0)
					{
						if($imageVert[$i][0][$y] == $search)
						{
							$bwcPos[$resultX] = $y;
							$resultX++;
							$search = 1;
						}
					} 
					else if($search == 1 && $resultX > 0)
					{
						if($imageVert[$i][0][$y] == $search)
						{
							$bwcPos[$resultX] = $y;
							$resultX++;
							$search = 0;
						}
					}
				}
				//echo $result . "<br>";
				if($resultX == 3 || $resultX == 4)
				{
					$number[$i] = 1;
				}
				else if($bwcPos[1] < round($height/2))
				{
					$number[$i] = 1;
				}
				else if($resultX < 3)
				{
					$number[$i] = 4;
				}
			}
		}
		$img['imgVert'] = $imageVert;
		$img['num'] = $number;
		return $img;
	}
	
	public function BWChanges()
	{
		$BWchange = array(array(array()));
		for($i = 0; $i < count($this->numberStripDimensions); $i++)
		{
			$dimensions = $this->numberStripDimensions[$i];
			
			//vertically
			for($y = $dimensions['startY']; $y < $dimensions['endY']; $y++)
			{
				$BWchange[$i]['vertically'][$y - $dimensions['startY']] = 0;
				$search = $this->imageByX[$dimensions['startX']][$y];
				
				//search for the next differently colored pixel to increase the counter for color changes
				switch($search)
				{
					case(0): //pixel is black
						$search = $this->white; 
						break;
					case($this->white): //pixel is white
						$search = 0;
						break;
					default:
						$search = $this->white;
						break;
				}
				for($x = $dimensions['startX']; $x < $dimensions['endX']; $x++)
				{
					if($this->imageByX[$x][$y] == $search)
					{
						$BWchange[$i]['vertically'][$y - $dimensions['startY']] += 1;
						if($search == $this->white)
						{
							$search = 0; //black
						} else
						{
							$search = $this->white; //white
						}
					}
				}
			}
			
			//horizontally
			for($x = $dimensions['startX']; $x < $dimensions['endX']; $x++)
			{
				$BWchange[$i]['horizontally'][$x - $dimensions['startX']] = 0;
				$search = $this->imageByX[$x][$dimensions['startY']];
				
				//search for the next differently colored pixel to increase the counter for color changes
				switch($search)
				{
					case(0): //pixel is black
						$search = $this->white; 
						break;
					case($this->white): //pixel is white
						$search = 0;
						break;
					default:
						$search = $this->white;
						break;
				}
				
				for($y = $dimensions['startY']; $y < $dimensions['endY']; $y++)
				{
					if($this->imageByX[$x][$y] == $search)
					{
						$BWchange[$i]['horizontally'][$x - $dimensions['startX']] += 1;
						if($search == $this->white)
						{
							$search = 0; //black
						} else
						{
							$search = $this->white; //white
						}
					}
				}
			}
		}
		return $BWchange;
	}
	
	protected function countField($startX,$endX,$startY,$endY)
	{
		$count = 0;
		for($x = $startX; $x < $endX; $x++)
		{
			for($y = $startY; $y < $endY; $y++)
			{
				if($this->imageByX[$x][$y] == 0)
				{
					$count++;
				}
			}
		}
		return $count;
	}
	
	public function pixelCount()
	{
		for($i = 0; $i < count($this->numberStripDimensions); $i++)
		{
			$height = $this->numberStripDimensions[$i]['endY'] - $this->numberStripDimensions[$i]['startY'];
			$width = $this->numberStripDimensions[$i]['endX'] - $this->numberStripDimensions[$i]['startX'];
			
			$this->height[$i] = $height;
			$this->width[$i] = $width;
			// 1:1 (1)
			$sX = $this->numberStripDimensions[$i]['startX'];
			$eX = $sX + floor($width/3);
			$sY = $this->numberStripDimensions[$i]['startY'];
			$eY = $sY + ($height/3);
			$count[$i][1] = $this->countField($sX,$eX,$sY,$eY);
			// 1:2 (2)
			$sX = $eX;
			$eX = $sX + floor($width/3);
			$count[$i][2] = $this->countField($sX,$eX,$sY,$eY);
			// 1:3 (3)
			$sX = $eX;
			$eX = $sX + floor($width/3);
			$count[$i][3] = $this->countField($sX,$eX,$sY,$eY);
			
			// 2:1 (4)
			$sX = $this->numberStripDimensions[$i]['startX'];
			$eX = $sX + floor($width/3);
			$sY = $eY;
			$eY = $sY + ($height/3);
			$count[$i][4] = $this->countField($sX,$eX,$sY,$eY);
			// 2:2 (5)
			$sX = $eX;
			$eX = $sX + floor($width/3);
			$count[$i][5] = $this->countField($sX,$eX,$sY,$eY);
			// 2:3 (6)
			$sX = $eX;
			$eX = $sX + floor($width/3);
			$count[$i][6] = $this->countField($sX,$eX,$sY,$eY);
			
			// 3:1 (7)
			$sX = $this->numberStripDimensions[$i]['startX'];
			$eX = $sX + floor($width/3);
			$sY = $eY;
			$eY = $sY + ($height/3);
			$count[$i][7] = $this->countField($sX,$eX,$sY,$eY);
			// 3:2 (8)
			$sX = $eX;
			$eX = $sX + floor($width/3);
			$count[$i][8] = $this->countField($sX,$eX,$sY,$eY);
			// 3:3 (9)
			$sX = $eX;
			$eX = $sX + floor($width/3);
			$count[$i][9] = $this->countField($sX,$eX,$sY,$eY);
		}
		return $count;
	}
	
	public function getNumberStripDimensionsArray() //returns 2D-Array with Start and End coordinates for each number 
	{
		$index = 0;
		$x = 1;
		$endOfFile = 0;
		while($endOfFile != END_OF_FILE)
		{
			$dimensions[$index] = $this->getNumberDimensions($x);
			$x = $dimensions[$index]['endX'];
			$endOfFile = $dimensions[$index]['startX'];
			$index++;
		}
		array_pop($dimensions); //delete last array index (contains END_OF_FILE information only) (is not a number)
		return $dimensions;
	}
	
	public function getNumberDimensions($offset = 0 /*X-Axis (px)*/) //returns array of Start and End coordinates for the next "visible" Number
	{	

		$dimensions['startX'] = $this->numberStartX($offset);
		if($dimensions != END_OF_FILE)
		{
			$dimensions['endX'] = $this->numberEndX($dimensions['startX']);
			$dimensions['startY'] = $this->numberStartY($dimensions['startX'],$dimensions['endX']);
			$dimensions['endY'] = $this->numberEndY($dimensions['startX'],$dimensions['endX'],$dimensions['startY']);
		}

		return $dimensions;
	}
	
	protected function numberStartX($offset) //start X-Axis
	{
		for($x = $offset; $x < $this->w; $x++)
		{
			for($y = 0; $y < $this->h; $y++)
			{
				if($this->imageByX[$x][$y] == 0)
				{
					return $x;
				}
			}
			if($x == $this->w - 1)
			{
				return END_OF_FILE;
			}
		}
	}
	
	protected function numberStartY($startX,$endX) //start Y-Axis
	{	
		for($y = 0; $y < $this->h; $y++)
		{
			for($x = $startX; $x <= $endX; $x++)
			{
				if($this->imageByY[$y][$x] == 0)
				{
					return $y;
				}
			}
		}
	}
	
	protected function numberEndY($startX,$endX,$offset) //end Y-Axis
	{	
		for($y = $offset; $y < $this->h; $y++)
		{
			$endOfNumber = true;
			for($x = $startX; $x <= $endX; $x++)
			{
				if($this->imageByY[$y][$x] == 0)
				{
					$endOfNumber = false;
				}
			}
			if($endOfNumber)
			{
				return $y;
			}
		}
	}
	
	protected function numberEndX($offset) //end X-Axis
	{	
		for($x = $offset; $x < $this->w; $x++)
		{
			$endOfNumber = true;
			for($y = 0; $y < $this->h; $y++)
			{
				if($this->imageByX[$x][$y] == 0)
				{
					$endOfNumber = false;
				}
			}
			if($endOfNumber)
			{
				return $x;
			}
		}
	}

}
function plot($r)
{
	foreach($r as $i => $array)
	{
		foreach($array as $num => $VertArr)
		{
			foreach($VertArr as $y => $value)
			{
				if($value == 0)
				{
					$top = $y + 100;
					$left = $num * 10 + $i * 60;
					echo "<p style='position: absolute; top: $top"."px; left: $left"."px;'>.</p>";
				}
			}
		}
	}
}

// for($o = 2; $o < 9; $o++)
// {
	// $image = new PNGNumberStrip("NumberStrip$o.png");
	// $r = $image->analyze2();
	// $cnt[$o] = 0;
	// $cntN[$o] = 0;
	// for($i = 0; $i < 10; $i++)
	// {
		// $cntnum[$o][$i] = 0;
		// $cntNnum[$o][$i] = 0;
		// if($r['num'][$i] == $i)
		// {
			// $cnt[$o]++;
			// $cntnum[$o][$i]++;
		// }
		// else
		// {
			// $cntN[$o]++;
			// $cntNnum[$o][$i]++;
		// }
	// }
// }

// echo "<pre>";
// echo "<br><br><br><br><br><br><br><br><br><br>analyze2 ";
// echo "richtige<br>";
// print_r($cnt);
// echo "<br>falsche<br>";
// print_r($cntN);


 //$image = new PNGNumberStrip("NumberStrip.png");
// $r = $image->analyze();
// print_r($r);
// $index = 0;
// foreach($image->pixelCountArrayInPercent as $field)
// {
	// in wirklichkeit horizontal
	// $vertical[$index][1] = $field[1] + $field[2] + $field[3];
	// $vertical[$index][2] = $field[4] + $field[5] + $field[6];
	// $vertical[$index][3] = $field[7] + $field[8] + $field[9];
	
	// in wirklichkeit vertikal
	// $horizontal[$index][1] = $field[1] + $field[4] + $field[7];
	// $horizontal[$index][2] = $field[2] + $field[5] + $field[8];
	// $horizontal[$index][3] = $field[3] + $field[6] + $field[9];
	
	// $index++;
// }	
// echo "<br>pixelCountArray<br>";
// print_r($image->pixelCountArrayInPercent);
// echo "<br>pixelCountVertikal<br>";
// print_r($vertical);
// echo "<br>pixelCountHorizontal<br>";
// print_r($horizontal);

echo '<img style="margin-left: 50%;" src="NumberStrip7.png"/>';
echo "<pre><br><br><br><br><br><br><br>";
$image = new PNGNumberStrip("NumberStrip7.png");
$s = $image->analyze2();
plot($s['imgVert']);
print_r($s['num']);

// $k = array();
// foreach($cntnum as $o => $cntl)
// {
	// foreach($cntl as $i => $val)
	// {
		// if($val == 1)
		// {
			// if(!isset($k[$i]))
				// $k[$i] = 1;
			// else
				// $k[$i]++;
		// }
	// }
// }

// print_r($k);
// print_r($cntnum);

$n = 0;
/* foreach($s as $t)
{
	echo "<pre> $n  1:1-3     " . ($t[1] + $t[2] + $t[3]);
	echo "   2:1-3     " . ($t[4] + $t[5] + $t[6]);
	echo "   3:1-3     " . ($t[7] + $t[8] + $t[9]) . "<br>";
	echo "</pre>";
	$n++;
}
echo "<br><br><br>";
$n=0;
foreach($s as $t)
{
	echo "<pre> $n  1-3:1     " . ($t[1] + $t[4] + $t[7]);
	echo "   1-3:2     " . ($t[2] + $t[5] + $t[8]);
	echo "   1-3:3     " . ($t[3] + $t[6] + $t[9]) . "<br>";
	echo "</pre>";
	$n++;
}
echo "</pre>"; */
/* for($z = 0; $z < 10; $z++)
{
	echo "<div style='left: ". $z * 10 ."%; position: absolute;'>";
	for($l = $image->numberStripDimensions[$z]['startY']; $l < $image->numberStripDimensions[$z]['endY']; $l++)
	{
		echo "<span style='background-color: rgb(" . $r[$z]['vertically'][$l - $image->numberStripDimensions[$z]['startY']] * 70 . "," . $r[$z]['vertically'][$l - $image->numberStripDimensions[$z]['startY']] * 70 . "," . $r[$z]['vertically'][$l - $image->numberStripDimensions[$z]['startY']] * 70 . ");'>";
		for($i = $image->numberStripDimensions[$z]['startX']; $i < $image->numberStripDimensions[$z]['endX']; $i++)
		{
			echo $image->imageByX[$i][$l];
		}
		echo "</span> ". $r[$z]['vertically'][$l - $image->numberStripDimensions[$z]['startY']] ."<br>";
	}
	echo "</div>";
}

for($z = 0; $z < 10; $z++)
{
	echo "<div style='left: ". $z * 10 ."%; top: 2000px; position: absolute;'>";
	for($l = $image->numberStripDimensions[$z]['startY']; $l < $image->numberStripDimensions[$z]['endY']; $l++)
	{
		for($i = $image->numberStripDimensions[$z]['startX']; $i < $image->numberStripDimensions[$z]['endX']; $i++)
		{
			echo "<span style='background-color: rgb(" . $r[$z]['horizontally'][$i - $image->numberStripDimensions[$z]['startX']] * 30 . "," . $r[$z]['horizontally'][$i - $image->numberStripDimensions[$z]['startX']] * 30 . "," . $r[$z]['horizontally'][$i - $image->numberStripDimensions[$z]['startX']] * 30 . ");'>";
			echo $image->imageByX[$i][$l];	
		}	
		echo "</span><br>";
	}
	echo "</div>";
}
echo "<pre>"; */

//print_r($image->BWChanges());


?>