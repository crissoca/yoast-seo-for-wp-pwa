<?php

/**
 * Plugin Name: Yoast SEO for WordPress PWA
 * Description: Makes Yoast SEO settings available to WordPress PWA using the REST API.
 * Author: Pablo Postigo, Luis Herranz, Niels Garve, Tedy Warsitha, Charlie Francis
 * Version: 1.7.0
 * Plugin URI: https://github.com/wp-pwa/yoast-seo-for-wp-pwa
 */

if ( class_exists( 'WPSEO_Frontend' ) ) {

	class Yoast_To_REST_API extends WPSEO_Frontend {

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		public static $instance;

		private static $parent = null;

		function __construct() {

			self::$parent = WPSEO_Frontend::get_instance();

			add_action( 'rest_api_init', [
				$this,
				'add_yoast_data'
			], 20 );

			add_filter( 'rest_prepare_latest', [
				$this,
				'add_yoast_to_latest'
			], 10 );
		}

		/**
		 * Get the singleton instance of this class.
		 *
		 * @return Yoast_To_REST_API
		 */
		public static function get_instance() {
			if ( ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Output the locale, doing some conversions to make sure the proper Facebook locale is outputted.
		 *
		 * Last update/compare with FB list done on 2015-03-16 by Rarst
		 *
		 * @see  http://www.facebook.com/translations/FacebookLocales.xml for the list of supported locales
		 *
		 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
		 *
		 * @param bool $echo Whether to echo or return the locale.
		 *
		 * @return string $locale
		 */
		function locale( $echo = true ) {
			/**
			 * Filter: 'wpseo_locale' - Allow changing the locale output.
			 *
			 * @api string $unsigned Locale string.
			 */
			$locale = apply_filters( 'wpseo_locale', get_locale() );

			// Catch some weird locales served out by WP that are not easily doubled up.
			$fix_locales = [
				'ca' => 'ca_ES',
				'en' => 'en_US',
				'el' => 'el_GR',
				'et' => 'et_EE',
				'ja' => 'ja_JP',
				'sq' => 'sq_AL',
				'uk' => 'uk_UA',
				'vi' => 'vi_VN',
				'zh' => 'zh_CN',
			];

			if ( isset( $fix_locales[ $locale ] ) ) {
				$locale = $fix_locales[ $locale ];
			}

			// Convert locales like "es" to "es_ES", in case that works for the given locale (sometimes it does).
			if ( strlen( $locale ) === 2 ) {
				$locale = strtolower( $locale ) . '_' . strtoupper( $locale );
			}

			// These are the locales FB supports.
			$fb_valid_fb_locales = [
				'af_ZA',
				// Afrikaans.
				'ak_GH',
				// Akan.
				'am_ET',
				// Amharic.
				'ar_AR',
				// Arabic.
				'as_IN',
				// Assamese.
				'ay_BO',
				// Aymara.
				'az_AZ',
				// Azerbaijani.
				'be_BY',
				// Belarusian.
				'bg_BG',
				// Bulgarian.
				'bp_IN',
				// Bhojpuri.
				'bn_IN',
				// Bengali.
				'br_FR',
				// Breton.
				'bs_BA',
				// Bosnian.
				'ca_ES',
				// Catalan.
				'cb_IQ',
				// Sorani Kurdish.
				'ck_US',
				// Cherokee.
				'co_FR',
				// Corsican.
				'cs_CZ',
				// Czech.
				'cx_PH',
				// Cebuano.
				'cy_GB',
				// Welsh.
				'da_DK',
				// Danish.
				'de_DE',
				// German.
				'el_GR',
				// Greek.
				'en_GB',
				// English (UK).
				'en_PI',
				// English (Pirate).
				'en_UD',
				// English (Upside Down).
				'en_US',
				// English (US).
				'em_ZM',
				'eo_EO',
				// Esperanto.
				'es_ES',
				// Spanish (Spain).
				'es_LA',
				// Spanish.
				'es_MX',
				// Spanish (Mexico).
				'et_EE',
				// Estonian.
				'eu_ES',
				// Basque.
				'fa_IR',
				// Persian.
				'fb_LT',
				// Leet Speak.
				'ff_NG',
				// Fulah.
				'fi_FI',
				// Finnish.
				'fo_FO',
				// Faroese.
				'fr_CA',
				// French (Canada).
				'fr_FR',
				// French (France).
				'fy_NL',
				// Frisian.
				'ga_IE',
				// Irish.
				'gl_ES',
				// Galician.
				'gn_PY',
				// Guarani.
				'gu_IN',
				// Gujarati.
				'gx_GR',
				// Classical Greek.
				'ha_NG',
				// Hausa.
				'he_IL',
				// Hebrew.
				'hi_IN',
				// Hindi.
				'hr_HR',
				// Croatian.
				'hu_HU',
				// Hungarian.
				'ht_HT',
				// Haitian Creole.
				'hy_AM',
				// Armenian.
				'id_ID',
				// Indonesian.
				'ig_NG',
				// Igbo.
				'is_IS',
				// Icelandic.
				'it_IT',
				// Italian.
				'ik_US',
				'iu_CA',
				'ja_JP',
				// Japanese.
				'ja_KS',
				// Japanese (Kansai).
				'jv_ID',
				// Javanese.
				'ka_GE',
				// Georgian.
				'kk_KZ',
				// Kazakh.
				'km_KH',
				// Khmer.
				'kn_IN',
				// Kannada.
				'ko_KR',
				// Korean.
				'ks_IN',
				// Kashmiri.
				'ku_TR',
				// Kurdish (Kurmanji).
				'ky_KG',
				// Kyrgyz.
				'la_VA',
				// Latin.
				'lg_UG',
				// Ganda.
				'li_NL',
				// Limburgish.
				'ln_CD',
				// Lingala.
				'lo_LA',
				// Lao.
				'lt_LT',
				// Lithuanian.
				'lv_LV',
				// Latvian.
				'mg_MG',
				// Malagasy.
				'mi_NZ',
				// Maori.
				'mk_MK',
				// Macedonian.
				'ml_IN',
				// Malayalam.
				'mn_MN',
				// Mongolian.
				'mr_IN',
				// Marathi.
				'ms_MY',
				// Malay.
				'mt_MT',
				// Maltese.
				'my_MM',
				// Burmese.
				'nb_NO',
				// Norwegian (bokmal).
				'nd_ZW',
				// Ndebele.
				'ne_NP',
				// Nepali.
				'nl_BE',
				// Dutch (Belgie).
				'nl_NL',
				// Dutch.
				'nn_NO',
				// Norwegian (nynorsk).
				'nr_ZA',
				// Southern Ndebele.
				'ns_ZA',
				// Northern Sotho.
				'ny_MW',
				// Chewa.
				'om_ET',
				// Oromo.
				'or_IN',
				// Oriya.
				'pa_IN',
				// Punjabi.
				'pl_PL',
				// Polish.
				'ps_AF',
				// Pashto.
				'pt_BR',
				// Portuguese (Brazil).
				'pt_PT',
				// Portuguese (Portugal).
				'qc_GT',
				// Quiché.
				'qu_PE',
				// Quechua.
				'qr_GR',
				'qz_MM',
				// Burmese (Zawgyi).
				'rm_CH',
				// Romansh.
				'ro_RO',
				// Romanian.
				'ru_RU',
				// Russian.
				'rw_RW',
				// Kinyarwanda.
				'sa_IN',
				// Sanskrit.
				'sc_IT',
				// Sardinian.
				'se_NO',
				// Northern Sami.
				'si_LK',
				// Sinhala.
				'su_ID',
				// Sundanese.
				'sk_SK',
				// Slovak.
				'sl_SI',
				// Slovenian.
				'sn_ZW',
				// Shona.
				'so_SO',
				// Somali.
				'sq_AL',
				// Albanian.
				'sr_RS',
				// Serbian.
				'ss_SZ',
				// Swazi.
				'st_ZA',
				// Southern Sotho.
				'sv_SE',
				// Swedish.
				'sw_KE',
				// Swahili.
				'sy_SY',
				// Syriac.
				'sz_PL',
				// Silesian.
				'ta_IN',
				// Tamil.
				'te_IN',
				// Telugu.
				'tg_TJ',
				// Tajik.
				'th_TH',
				// Thai.
				'tk_TM',
				// Turkmen.
				'tl_PH',
				// Filipino.
				'tl_ST',
				// Klingon.
				'tn_BW',
				// Tswana.
				'tr_TR',
				// Turkish.
				'ts_ZA',
				// Tsonga.
				'tt_RU',
				// Tatar.
				'tz_MA',
				// Tamazight.
				'uk_UA',
				// Ukrainian.
				'ur_PK',
				// Urdu.
				'uz_UZ',
				// Uzbek.
				've_ZA',
				// Venda.
				'vi_VN',
				// Vietnamese.
				'wo_SN',
				// Wolof.
				'xh_ZA',
				// Xhosa.
				'yi_DE',
				// Yiddish.
				'yo_NG',
				// Yoruba.
				'zh_CN',
				// Simplified Chinese (China).
				'zh_HK',
				// Traditional Chinese (Hong Kong).
				'zh_TW',
				// Traditional Chinese (Taiwan).
				'zu_ZA',
				// Zulu.
				'zz_TR',
				// Zazaki.
			];

			// Check to see if the locale is a valid FB one, if not, use en_US as a fallback.
			if ( ! in_array( $locale, $fb_valid_fb_locales, true ) ) {
				$locale = strtolower( substr( $locale, 0, 2 ) ) . '_' . strtoupper( substr( $locale, 0, 2 ) );
				if ( ! in_array( $locale, $fb_valid_fb_locales, true ) ) {
					$locale = 'en_US';
				}
			}

			if ( $echo !== false ) {
				self::$parent->og_tag( 'og:locale', $locale );
			}

			return $locale;
		}

		function add_yoast_data() {

			// Custom post types (including posts and pages):
			$types = get_post_types( [ 'public' => true ] );

			foreach ( $types as $key => $type ) {
				register_rest_field(
					$type,
					'yoast_meta',
					[
						'get_callback'    => [
							$this,
							'wp_api_encode_yoast'
						],
						'update_callback' => null,
						'schema'          => null,
					]
				);
			}

			// Category and Tag
			register_rest_field( [
				'category',
				'tag',
				'user'
			],
				'yoast_meta',
				[
					'get_callback'    => [
						$this,
						'wp_api_encode_archives'
					],
					'update_callback' => null,
					'schema'          => null,
				]
			);

		}


		function wp_api_encode_yoast( $p, $field_name, WP_REST_Request $request ) {

			$parameters = $request->get_query_params();

			if ( true === isset( $parameters['per_page'] ) && 1 !== $parameters['per_page'] ) {
				return false;
			}

			$args = [
				'p'         => $p['id'],
				'post_type' => 'any'
			];

			$GLOBALS['wp_query'] = new WP_Query( $args );

			$yoast_meta = [
				'title'     => html_entity_decode( self::$parent->title( '' ), ENT_QUOTES, "UTF-8" ),
				'metadesc'  => html_entity_decode( self::$parent->metadesc( false ), ENT_QUOTES, "UTF-8" ),
				'canonical' => self::$parent->canonical( false ),
				'locale'    => self::locale( false ),
			];

			wp_reset_postdata();

			return $yoast_meta;

		}

		function wp_api_encode_archives( $p, $field_name, WP_REST_Request $request ) {

			$route      = $request->get_route();
			$parameters = $request->get_query_params();

			if ( is_admin() ) {
				return false;
			}

			if ( true === isset( $parameters['hide_empty'] ) ) {
				return false;
			}

			switch ( $route ) :

				case "/wp/v2/categories" :
					$args = [
						'cat' => $p['id'],
					];
					break;

				case "/wp/v2/tags" :
					$args = [
						'tag_id' => $p['id'],
					];
					break;

				case "/wp/v2/users" :
					$args = [
						'author' => $p['id'],
					];
					break;

			endswitch;

			if ( isset( $args ) ) {

				query_posts( $args );

				if ( have_posts() )
					the_post();
			}

			$yoast_meta = [
				'title'     => html_entity_decode( self::$parent->title( '' ), ENT_QUOTES, "UTF-8" ),
				'metadesc'  => html_entity_decode( self::$parent->metadesc( false ), ENT_QUOTES, "UTF-8" ),
				'canonical' => self::$parent->canonical( false ),
				'locale'    => self::locale( false ),
			];

			wp_reset_query();

			return $yoast_meta;
		}

		function add_yoast_to_latest( $response ) {

			$data = $response->data;

			$data['yoast_meta'] = [
				'title' => html_entity_decode( self::$parent->title( '' ), ENT_QUOTES, "UTF-8" )
			];

			return rest_ensure_response( $data );
		}
	}
}

function WPAPIYoast_init() {

	if ( class_exists( 'WPSEO_Frontend' ) ) {

		Yoast_To_REST_API::get_instance();
	}
}

add_action( 'rest_api_init', 'WPAPIYoast_init' );
