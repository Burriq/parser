<?php

namespace App\Feeds\Vendors\JEF;

use App\Feeds\Parser\HtmlParser;
use App\Helpers\StringHelper;
use App\Feeds\Utils\ParserCrawler;

class Parser extends HtmlParser
{
  public function getProduct(): string
    {
      return trim( $this->getAttr( 'meta[property="og:title"]', 'content' ) );
    }

  public function getMpn(): string
    {
      return $this->getText('sku');
    }

  public function getDescription(): string
  {
    return trim( $this->getAttr( 'meta[property="og:description"]', 'content' ) );
  }

  public function getImages(): array
    {
      return array_values( array_unique( $this->getAttr( 'meta[property="og:image"]', 'content' )  ) );
    }

  public function getCostToUs(): float
    {
      return StringHelper::getMoney($this->getAttr( 'meta[property="og:price"]', 'content' ));
    }

  public function getAvail(): ?int
    {
      $stock_status = $this->getAttr('meta[itemprop="availability"]', 'content');
      return $stock_status === 'InStock' ? self::DEFAULT_AVAIL_NUMBER : 0;
    }

  public function getOptions(): array
    {
        $options = [];
        $option_lists = $this->filter( '.product-options' );

        if ( !$option_lists->count() ) {
            return $options;
        }

        $option_lists->each( function ( ParserCrawler $list ) use ( &$options ) {
            $label = $list->filter( 'label' );
            if ( $label->count() === 0 ) {
                return;
            }
            $name = trim( $label->text(), ' : ' );
            $options[ $name ] = [];
            $list->filter( 'option' )->each( function ( ParserCrawler $option ) use ( &$options, $name ) {
                $options[ $name ][] = trim( $option->text(), '  ' );
            } );
        } );

        return $options;
    }

}