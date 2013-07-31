# Imagebinder plugin for CakePHP

## Introduction

PHPMatsuri2013（http://www.phpmatsuri.net/2013/）の前日の飲み会で出た話題を発端に思いついたネタを、初日いっぱい掛けて実装して、なんとか翌日のLTに間に合わせたものに、readmeなどを追加したアーカイブです。

私が普段愛用しているFilebinderプラグイン（https://github.com/fusic/filebinder）はもちろん画像も取り扱えますが、添付した画像の動的なリサイズがありません。

Filebinder開発者のk1LoW氏（https://github.com/k1LoW）に確認したところ、Filebinderが扱うファイルは画像とは限らないのでリサイズの機能を含めることが出来ないとのことなので、Filebinderに機能追加するプラグインとしてImagebinderを開発しました。

名前をImage〜にすることで画像に特化した動きをさせてます。
Filebinderを既に導入したプロジェクトにも追加導入が可能です。むしろ、プロジェクトにFilebinderがなければ動きません。Imagebinderしか使わない場合でもFilebinderを導入してください。

## Requirements

- PHP >= 5.2.6
- CakePHP >= 2.0

## Installation

まずFilebinderをインストールします。
https://github.com/fusic/filebinder

次にImagebinderをダウンロードして'Imagebinder'のディレクトリ名でapp/plugins以下に配置してください。

次にbootstrap.phpに以下を追記します

    <?php
        CakePlugin::load('Imagebinder');



## Usage

基本的な使い方はFilebinderとほぼ同じですが、いつくか変更点がございます。

コントローラーのコンポーネント呼び出しを以下のように変更する。

	<php
		class HogesController extends AppController {
		    public $components = array(
        		'Ring' => array('className'=>'Imagebinder.ImageRing'),
		    );


Filebinderだと画像を表示するView側で以下のように書きますが、

      <?php echo $this->Label->image($mergedData['Post']['image']);?> 

これを

      <?php echo $this->ImageLabel->image($mergedData['Post']['image'], array('width'=>'300'));?> 

という風にImageLabelヘルパーに置き換えてください。
第二引数にwidthのパラメータをつけることで指定した幅に最適化した画像を動的に出力します。

## Cache

Filebinderでは一度出力したファイルを指定したディレクトリに保管してCacheする機構がございます。Imagebinderではこの機構を利用してリサイズした画像ファイルをCacheさせてます。

	<?php
	    public $bindFields = array(
    	    array(
        	    'field' => 'image',
        	    'tmpPath' => '/var/www/html/myapp/app/webroot/files/cache/',
        	    'filePath' => '/var/www/html/myapp/app/webroot/files/',
    	    ),
    	);


であれば、

	/var/www/html/myapp/app/webroot/files/モデル名/ID値/image/WIDTH値

以下に画像ファイルを出力します。ファイルが存在していれば再生成はしません。

## Special Thanks

k1LoW
https://github.com/k1LoW
https://github.com/fusic/filebinder

saku
https://github.com/kozo
https://github.com/kozo/imagemake

画像のリサイズにimagemakeコンポーネントを利用させてもらいました

## License

The MIT License

COPYRIGHTS (C) 2000-2013 Web-Promotions Limited. All Rights Reserved. (http://www.web-prom.net/)

