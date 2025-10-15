<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\Compute;

class ResourceStatusPhysicalHostTopology extends \Google\Model
{
  /**
   * @var string
   */
  public $block;
  /**
   * @var string
   */
  public $cluster;
  /**
   * @var string
   */
  public $host;
  /**
   * @var string
   */
  public $subblock;

  /**
   * @param string
   */
  public function setBlock($block)
  {
    $this->block = $block;
  }
  /**
   * @return string
   */
  public function getBlock()
  {
    return $this->block;
  }
  /**
   * @param string
   */
  public function setCluster($cluster)
  {
    $this->cluster = $cluster;
  }
  /**
   * @return string
   */
  public function getCluster()
  {
    return $this->cluster;
  }
  /**
   * @param string
   */
  public function setHost($host)
  {
    $this->host = $host;
  }
  /**
   * @return string
   */
  public function getHost()
  {
    return $this->host;
  }
  /**
   * @param string
   */
  public function setSubblock($subblock)
  {
    $this->subblock = $subblock;
  }
  /**
   * @return string
   */
  public function getSubblock()
  {
    return $this->subblock;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ResourceStatusPhysicalHostTopology::class, 'Google_Service_Compute_ResourceStatusPhysicalHostTopology');
