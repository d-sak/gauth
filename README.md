# Google Account API(OAuth2.0)とGoogle DocsのACLを利用した認証・認可

"gauth" GoogleAccountでいっちゃんいけてる認証方法を考えたよ
http://d.hatena.ne.jp/d_sak/20120202/1328147432

## 依存するextension
httpsアクセスを行うので、opensslが有効になっている必要があります。

## 使い方

https://code.google.com/apis/console/

で、新規プロジェクトを作成、API Accessの「Create an OAuth 2.0 client id」から
client_id,client_secretを取得する。

次に、Google Docsでカラのドキュメントを作成。そのURLのidをdoc_idにする。

配置したphpのURLを元にcallbackを設定

## TODO 
+ session管理を利便性を担保したまま抽象化できないか

## 解決した
+ classにする(staticにして利便性を損なわないように)
+ curl使わない

## 関連URL
+ http://project-p.jp/halt/?p=1715
+ http://d.hatena.ne.jp/d_sak/20120202/1328147432
+ http://www.slideshare.net/dai861/gauth
