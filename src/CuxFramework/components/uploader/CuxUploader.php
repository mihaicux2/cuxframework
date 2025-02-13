<?php

/**
 * CuxUploader class file
 * 
 * @package Components
 * @subpackage Uploader
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\uploader;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;

/**
 * Simple class that can be used to handle uploaded files.
 * It can also generate thumbnails for image files
 */
class CuxUploader extends CuxBaseObject {

    /**
     * Directory name prefix for the generated thumbnail folders 
     * @var string
     */
    private $_prefix;
    
    /**
     * The list of known file ( mime ) types
     * @var array
     */
    private $mimeTypes = array(
        '3dm' => 'x-world/x-3dmf',
        '3dmf' => 'x-world/x-3dmf',
        '3dml' => 'text/vnd.in3d.3dml',
        '3ds' => 'image/x-3ds',
        '3g2' => 'video/3gpp2',
        '3gp' => 'video/3gpp',
        '7z' => 'application/x-7z-compressed',
        'a' => 'application/octet-stream',
        'aab' => 'application/x-authorware-bin',
        'aac' => 'audio/x-aac',
        'aam' => 'application/x-authorware-map',
        'aas' => 'application/x-authorware-seg',
        'abc' => 'text/vnd.abc',
        'abw' => 'application/x-abiword',
        'ac' => 'application/pkix-attr-cert',
        'acc' => 'application/vnd.americandynamics.acc',
        'ace' => 'application/x-ace-compressed',
        'acgi' => 'text/html',
        'acu' => 'application/vnd.acucobol',
        'acutc' => 'application/vnd.acucorp',
        'adp' => 'audio/adpcm',
        'aep' => 'application/vnd.audiograph',
        'afl' => 'video/animaflex',
        'afm' => 'application/x-font-type1',
        'afp' => 'application/vnd.ibm.modcap',
        'ahead' => 'application/vnd.ahead.space',
        'ai' => 'application/postscript',
        'aif' => 'audio/aiff',
        'aifc' => 'audio/aiff',
        'aiff' => 'audio/aiff',
        'aim' => 'application/x-aim',
        'aip' => 'text/x-audiosoft-intra',
        'air' => 'application/vnd.adobe.air-application-installer-package+zip',
        'ait' => 'application/vnd.dvb.ait',
        'ami' => 'application/vnd.amiga.ami',
        'ani' => 'application/x-navi-animation',
        'aos' => 'application/x-nokia-9000-communicator-add-on-software',
        'apk' => 'application/vnd.android.package-archive',
        'appcache' => 'text/cache-manifest',
        'application' => 'application/x-ms-application',
        'apr' => 'application/vnd.lotus-approach',
        'aps' => 'application/mime',
        'arc' => 'application/x-freearc',
        'arj' => 'application/arj',
        'art' => 'image/x-jg',
        'asc' => 'application/pgp-signature',
        'asf' => 'video/x-ms-asf',
        'asm' => 'text/x-asm',
        'aso' => 'application/vnd.accpac.simply.aso',
        'asp' => 'text/asp',
        'asx' => 'application/x-mplayer2',
        'atc' => 'application/vnd.acucorp',
        'atom' => 'application/atom+xml',
        'atomcat' => 'application/atomcat+xml',
        'atomsvc' => 'application/atomsvc+xml',
        'atx' => 'application/vnd.antix.game-component',
        'au' => 'audio/basic',
        'avi' => 'application/x-troff-msvideo',
        'avs' => 'video/avs-video',
        'aw' => 'application/applixware',
        'azf' => 'application/vnd.airzip.filesecure.azf',
        'azs' => 'application/vnd.airzip.filesecure.azs',
        'azw' => 'application/vnd.amazon.ebook',
        'bat' => 'application/x-msdownload',
        'bcpio' => 'application/x-bcpio',
        'bdf' => 'application/x-font-bdf',
        'bdm' => 'application/vnd.syncml.dm+wbxml',
        'bed' => 'application/vnd.realvnc.bed',
        'bh2' => 'application/vnd.fujitsu.oasysprs',
        'bin' => 'application/mac-binary',
        'blb' => 'application/x-blorb',
        'blorb' => 'application/x-blorb',
        'bm' => 'image/bmp',
        'bmi' => 'application/vnd.bmi',
        'bmp' => 'image/bmp',
        'boo' => 'application/book',
        'book' => 'application/vnd.framemaker',
        'box' => 'application/vnd.previewsystems.box',
        'boz' => 'application/x-bzip2',
        'bpk' => 'application/octet-stream',
        'bsh' => 'application/x-bsh',
        'btif' => 'image/prs.btif',
        'buffer' => 'application/octet-stream',
        'bz' => 'application/x-bzip',
        'bz2' => 'application/x-bzip2',
        'c' => 'text/x-c',
        'c++' => 'text/plain',
        'c9amc' => 'application/vnd.cluetrust.cartomobile-config',
        'c9amz' => 'application/vnd.cluetrust.cartomobile-config-pkg',
        'c4d' => 'application/vnd.clonk.c4group',
        'c4f' => 'application/vnd.clonk.c4group',
        'c4g' => 'application/vnd.clonk.c4group',
        'c4p' => 'application/vnd.clonk.c4group',
        'c4u' => 'application/vnd.clonk.c4group',
        'cab' => 'application/vnd.ms-cab-compressed',
        'caf' => 'audio/x-caf',
        'cap' => 'application/vnd.tcpdump.pcap',
        'car' => 'application/vnd.curl.car',
        'cat' => 'application/vnd.ms-pki.seccat',
        'cb7' => 'application/x-cbr',
        'cba' => 'application/x-cbr',
        'cbr' => 'application/x-cbr',
        'cbt' => 'application/x-cbr',
        'cbz' => 'application/x-cbr',
        'cc' => 'text/plain',
        'ccad' => 'application/clariscad',
        'cco' => 'application/x-cocoa',
        'cct' => 'application/x-director',
        'ccxml' => 'application/ccxml+xml',
        'cdbcmsg' => 'application/vnd.contact.cmsg',
        'cdf' => 'application/cdf',
        'cdkey' => 'application/vnd.mediastation.cdkey',
        'cdmia' => 'application/cdmi-capability',
        'cdmic' => 'application/cdmi-container',
        'cdmid' => 'application/cdmi-domain',
        'cdmio' => 'application/cdmi-object',
        'cdmiq' => 'application/cdmi-queue',
        'cdx' => 'chemical/x-cdx',
        'cdxml' => 'application/vnd.chemdraw+xml',
        'cdy' => 'application/vnd.cinderella',
        'cer' => 'application/pkix-cert',
        'cfs' => 'application/x-cfs-compressed',
        'cgm' => 'image/cgm',
        'cha' => 'application/x-chat',
        'chat' => 'application/x-chat',
        'chm' => 'application/vnd.ms-htmlhelp',
        'chrt' => 'application/vnd.kde.kchart',
        'cif' => 'chemical/x-cif',
        'cii' => 'application/vnd.anser-web-certificate-issue-initiation',
        'cil' => 'application/vnd.ms-artgalry',
        'cla' => 'application/vnd.claymore',
        'class' => 'application/java',
        'clkk' => 'application/vnd.crick.clicker.keyboard',
        'clkp' => 'application/vnd.crick.clicker.palette',
        'clkt' => 'application/vnd.crick.clicker.template',
        'clkw' => 'application/vnd.crick.clicker.wordbank',
        'clkx' => 'application/vnd.crick.clicker',
        'clp' => 'application/x-msclip',
        'cmc' => 'application/vnd.cosmocaller',
        'cmdf' => 'chemical/x-cmdf',
        'cml' => 'chemical/x-cml',
        'cmp' => 'application/vnd.yellowriver-custom-menu',
        'cmx' => 'image/x-cmx',
        'cod' => 'application/vnd.rim.cod',
        'com' => 'application/octet-stream',
        'conf' => 'text/plain',
        'cpio' => 'application/x-cpio',
        'cpp' => 'text/x-c',
        'cpt' => 'application/x-compactpro',
        'crd' => 'application/x-mscardfile',
        'crl' => 'application/pkcs-crl',
        'crt' => 'application/pkix-cert',
        'crx' => 'application/x-chrome-extension',
        'cryptonote' => 'application/vnd.rig.cryptonote',
        'csh' => 'application/x-csh',
        'csml' => 'chemical/x-csml',
        'csp' => 'application/vnd.commonspace',
        'css' => 'text/css',
        'cst' => 'application/x-director',
        'csv' => 'text/csv',
        'cu' => 'application/cu-seeme',
        'curl' => 'text/vnd.curl',
        'cww' => 'application/prs.cww',
        'cxt' => 'application/x-director',
        'cxx' => 'text/x-c',
        'dae' => 'model/vnd.collada+xml',
        'daf' => 'application/vnd.mobius.daf',
        'dart' => 'application/vnd.dart',
        'dataless' => 'application/vnd.fdsn.seed',
        'davmount' => 'application/davmount+xml',
        'dbk' => 'application/docbook+xml',
        'dcr' => 'application/x-director',
        'dcurl' => 'text/vnd.curl.dcurl',
        'dd2' => 'application/vnd.oma.dd2+xml',
        'ddd' => 'application/vnd.fujixerox.ddd',
        'deb' => 'application/x-debian-package',
        'deepv' => 'application/x-deepv',
        'def' => 'text/plain',
        'deploy' => 'application/octet-stream',
        'der' => 'application/x-x509-ca-cert',
        'dfac' => 'application/vnd.dreamfactory',
        'dgc' => 'application/x-dgc-compressed',
        'dic' => 'text/x-c',
        'dif' => 'video/x-dv',
        'diff' => 'text/plain',
        'dir' => 'application/x-director',
        'dis' => 'application/vnd.mobius.dis',
        'dist' => 'application/octet-stream',
        'distz' => 'application/octet-stream',
        'djv' => 'image/vnd.djvu',
        'djvu' => 'image/vnd.djvu',
        'dl' => 'video/dl',
        'dll' => 'application/x-msdownload',
        'dmg' => 'application/x-apple-diskimage',
        'dmp' => 'application/vnd.tcpdump.pcap',
        'dms' => 'application/octet-stream',
        'dna' => 'application/vnd.dna',
        'doc' => 'application/msword',
        'docm' => 'application/vnd.ms-word.document.macroenabled.12',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dot' => 'application/msword',
        'dotm' => 'application/vnd.ms-word.template.macroenabled.12',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dp' => 'application/vnd.osgi.dp',
        'dpg' => 'application/vnd.dpgraph',
        'dra' => 'audio/vnd.dra',
        'drw' => 'application/drafting',
        'dsc' => 'text/prs.lines.tag',
        'dssc' => 'application/dssc+der',
        'dtb' => 'application/x-dtbook+xml',
        'dtd' => 'application/xml-dtd',
        'dts' => 'audio/vnd.dts',
        'dtshd' => 'audio/vnd.dts.hd',
        'dump' => 'application/octet-stream',
        'dv' => 'video/x-dv',
        'dvb' => 'video/vnd.dvb.file',
        'dvi' => 'application/x-dvi',
        'dwf' => 'model/vnd.dwf',
        'dwg' => 'image/vnd.dwg',
        'dxf' => 'image/vnd.dxf',
        'dxp' => 'application/vnd.spotfire.dxp',
        'dxr' => 'application/x-director',
        'ecelp4800' => 'audio/vnd.nuera.ecelp4800',
        'ecelp7470' => 'audio/vnd.nuera.ecelp7470',
        'ecelp9600' => 'audio/vnd.nuera.ecelp9600',
        'ecma' => 'application/ecmascript',
        'edm' => 'application/vnd.novadigm.edm',
        'edx' => 'application/vnd.novadigm.edx',
        'efif' => 'application/vnd.picsel',
        'ei6' => 'application/vnd.pg.osasli',
        'el' => 'text/x-script.elisp',
        'elc' => 'application/x-elc',
        'emf' => 'application/x-msmetafile',
        'eml' => 'message/rfc822',
        'emma' => 'application/emma+xml',
        'emz' => 'application/x-msmetafile',
        'env' => 'application/x-envoy',
        'eol' => 'audio/vnd.digital-winds',
        'eot' => 'application/vnd.ms-fontobject',
        'eps' => 'application/postscript',
        'epub' => 'application/epub+zip',
        'es' => 'application/x-esrehber',
        'es3' => 'application/vnd.eszigno3+xml',
        'esa' => 'application/vnd.osgi.subsystem',
        'esf' => 'application/vnd.epson.esf',
        'et3' => 'application/vnd.eszigno3+xml',
        'etx' => 'text/x-setext',
        'eva' => 'application/x-eva',
        'event-stream' => 'text/event-stream',
        'evy' => 'application/envoy',
        'exe' => 'application/x-msdownload',
        'exi' => 'application/exi',
        'ext' => 'application/vnd.novadigm.ext',
        'ez' => 'application/andrew-inset',
        'ez2' => 'application/vnd.ezpix-album',
        'ez3' => 'application/vnd.ezpix-package',
        'f' => 'text/plain',
        'f4v' => 'video/x-f4v',
        'f77' => 'text/x-fortran',
        'f90' => 'text/plain',
        'fbs' => 'image/vnd.fastbidsheet',
        'fcdt' => 'application/vnd.adobe.formscentral.fcdt',
        'fcs' => 'application/vnd.isac.fcs',
        'fdf' => 'application/vnd.fdf',
        'fe_launch' => 'application/vnd.denovo.fcselayout-link',
        'fg5' => 'application/vnd.fujitsu.oasysgp',
        'fgd' => 'application/x-director',
        'fh' => 'image/x-freehand',
        'fh4' => 'image/x-freehand',
        'fh5' => 'image/x-freehand',
        'fh7' => 'image/x-freehand',
        'fhc' => 'image/x-freehand',
        'fif' => 'image/fif',
        'fig' => 'application/x-xfig',
        'flac' => 'audio/flac',
        'fli' => 'video/fli',
        'flo' => 'application/vnd.micrografx.flo',
        'flv' => 'video/x-flv',
        'flw' => 'application/vnd.kde.kivio',
        'flx' => 'text/vnd.fmi.flexstor',
        'fly' => 'text/vnd.fly',
        'fm' => 'application/vnd.framemaker',
        'fmf' => 'video/x-atomic3d-feature',
        'fnc' => 'application/vnd.frogans.fnc',
        'for' => 'text/plain',
        'fpx' => 'image/vnd.fpx',
        'frame' => 'application/vnd.framemaker',
        'frl' => 'application/freeloader',
        'fsc' => 'application/vnd.fsc.weblaunch',
        'fst' => 'image/vnd.fst',
        'ftc' => 'application/vnd.fluxtime.clip',
        'fti' => 'application/vnd.anser-web-funds-transfer-initiation',
        'funk' => 'audio/make',
        'fvt' => 'video/vnd.fvt',
        'fxp' => 'application/vnd.adobe.fxp',
        'fxpl' => 'application/vnd.adobe.fxp',
        'fzs' => 'application/vnd.fuzzysheet',
        'g' => 'text/plain',
        'g2w' => 'application/vnd.geoplan',
        'g3' => 'image/g3fax',
        'g3w' => 'application/vnd.geospace',
        'gac' => 'application/vnd.groove-account',
        'gam' => 'application/x-tads',
        'gbr' => 'application/rpki-ghostbusters',
        'gca' => 'application/x-gca-compressed',
        'gdl' => 'model/vnd.gdl',
        'geo' => 'application/vnd.dynageo',
        'gex' => 'application/vnd.geometry-explorer',
        'ggb' => 'application/vnd.geogebra.file',
        'ggt' => 'application/vnd.geogebra.tool',
        'ghf' => 'application/vnd.groove-help',
        'gif' => 'image/gif',
        'gim' => 'application/vnd.groove-identity-message',
        'gl' => 'video/gl',
        'gml' => 'application/gml+xml',
        'gmx' => 'application/vnd.gmx',
        'gnumeric' => 'application/x-gnumeric',
        'gph' => 'application/vnd.flographit',
        'gpx' => 'application/gpx+xml',
        'gqf' => 'application/vnd.grafeq',
        'gqs' => 'application/vnd.grafeq',
        'gram' => 'application/srgs',
        'gramps' => 'application/x-gramps-xml',
        'gre' => 'application/vnd.geometry-explorer',
        'grv' => 'application/vnd.groove-injector',
        'grxml' => 'application/srgs+xml',
        'gsd' => 'audio/x-gsm',
        'gsf' => 'application/x-font-ghostscript',
        'gsm' => 'audio/x-gsm',
        'gsp' => 'application/x-gsp',
        'gss' => 'application/x-gss',
        'gtar' => 'application/x-gtar',
        'gtm' => 'application/vnd.groove-tool-message',
        'gtw' => 'model/vnd.gtw',
        'gv' => 'text/vnd.graphviz',
        'gxf' => 'application/gxf',
        'gxt' => 'application/vnd.geonext',
        'gz' => 'application/x-compressed',
        'gzip' => 'application/x-gzip',
        'h' => 'text/plain',
        'h261' => 'video/h261',
        'h263' => 'video/h263',
        'h264' => 'video/h264',
        'hal' => 'application/vnd.hal+xml',
        'hbci' => 'application/vnd.hbci',
        'hdf' => 'application/x-hdf',
        'help' => 'application/x-helpfile',
        'hgl' => 'application/vnd.hp-hpgl',
        'hh' => 'text/plain',
        'hlb' => 'text/x-script',
        'hlp' => 'application/hlp',
        'hpg' => 'application/vnd.hp-hpgl',
        'hpgl' => 'application/vnd.hp-hpgl',
        'hpid' => 'application/vnd.hp-hpid',
        'hps' => 'application/vnd.hp-hps',
        'hqx' => 'application/binhex',
        'hta' => 'application/hta',
        'htc' => 'text/x-component',
        'htke' => 'application/vnd.kenameaapp',
        'htm' => 'text/html',
        'html' => 'text/html',
        'htmls' => 'text/html',
        'htt' => 'text/webviewhtml',
        'htx' => 'text/html',
        'hvd' => 'application/vnd.yamaha.hv-dic',
        'hvp' => 'application/vnd.yamaha.hv-voice',
        'hvs' => 'application/vnd.yamaha.hv-script',
        'i2g' => 'application/vnd.intergeo',
        'icc' => 'application/vnd.iccprofile',
        'ice' => 'x-conference/x-cooltalk',
        'icm' => 'application/vnd.iccprofile',
        'ico' => 'image/x-icon',
        'ics' => 'text/calendar',
        'idc' => 'text/plain',
        'ief' => 'image/ief',
        'iefs' => 'image/ief',
        'ifb' => 'text/calendar',
        'ifm' => 'application/vnd.shana.informed.formdata',
        'iges' => 'application/iges',
        'igl' => 'application/vnd.igloader',
        'igm' => 'application/vnd.insors.igm',
        'igs' => 'application/iges',
        'igx' => 'application/vnd.micrografx.igx',
        'iif' => 'application/vnd.shana.informed.interchange',
        'ima' => 'application/x-ima',
        'imap' => 'application/x-httpd-imap',
        'imp' => 'application/vnd.accpac.simply.imp',
        'ims' => 'application/vnd.ms-ims',
        'in' => 'text/plain',
        'inf' => 'application/inf',
        'ink' => 'application/inkml+xml',
        'inkml' => 'application/inkml+xml',
        'ins' => 'application/x-internett-signup',
        'install' => 'application/x-install-instructions',
        'iota' => 'application/vnd.astraea-software.iota',
        'ip' => 'application/x-ip2',
        'ipfix' => 'application/ipfix',
        'ipk' => 'application/vnd.shana.informed.package',
        'irm' => 'application/vnd.ibm.rights-management',
        'irp' => 'application/vnd.irepository.package+xml',
        'iso' => 'application/x-iso9660-image',
        'isu' => 'video/x-isvideo',
        'it' => 'audio/it',
        'itp' => 'application/vnd.shana.informed.formtemplate',
        'iv' => 'application/x-inventor',
        'ivp' => 'application/vnd.immervision-ivp',
        'ivr' => 'i-world/i-vrml',
        'ivu' => 'application/vnd.immervision-ivu',
        'ivy' => 'application/x-livescreen',
        'jad' => 'text/vnd.sun.j2me.app-descriptor',
        'jam' => 'application/vnd.jam',
        'jar' => 'application/java-archive',
        'jav' => 'text/plain',
        'java' => 'text/plain',
        'jcm' => 'application/x-java-commerce',
        'jfif' => 'image/jpeg',
        'jfif-tbnl' => 'image/jpeg',
        'jisp' => 'application/vnd.jisp',
        'jlt' => 'application/vnd.hp-jlyt',
        'jnlp' => 'application/x-java-jnlp-file',
        'joda' => 'application/vnd.joost.joda-archive',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpgm' => 'video/jpm',
        'jpgv' => 'video/jpeg',
        'jpm' => 'video/jpm',
        'jps' => 'image/x-jps',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'jsonml' => 'application/jsonml+json',
        'jut' => 'image/jutvision',
        'kar' => 'audio/midi',
        'karbon' => 'application/vnd.kde.karbon',
        'kfo' => 'application/vnd.kde.kformula',
        'kia' => 'application/vnd.kidspiration',
        'kil' => 'application/x-killustrator',
        'kml' => 'application/vnd.google-earth.kml+xml',
        'kmz' => 'application/vnd.google-earth.kmz',
        'kne' => 'application/vnd.kinar',
        'knp' => 'application/vnd.kinar',
        'kon' => 'application/vnd.kde.kontour',
        'kpr' => 'application/vnd.kde.kpresenter',
        'kpt' => 'application/vnd.kde.kpresenter',
        'kpxx' => 'application/vnd.ds-keypoint',
        'ksh' => 'application/x-ksh',
        'ksp' => 'application/vnd.kde.kspread',
        'ktr' => 'application/vnd.kahootz',
        'ktx' => 'image/ktx',
        'ktz' => 'application/vnd.kahootz',
        'kwd' => 'application/vnd.kde.kword',
        'kwt' => 'application/vnd.kde.kword',
        'la' => 'audio/nspaudio',
        'lam' => 'audio/x-liveaudio',
        'lasxml' => 'application/vnd.las.las+xml',
        'latex' => 'application/x-latex',
        'lbd' => 'application/vnd.llamagraphics.life-balance.desktop',
        'lbe' => 'application/vnd.llamagraphics.life-balance.exchange+xml',
        'les' => 'application/vnd.hhe.lesson-player',
        'lha' => 'application/lha',
        'lhx' => 'application/octet-stream',
        'link66' => 'application/vnd.route66.link66+xml',
        'list' => 'text/plain',
        'list3820' => 'application/vnd.ibm.modcap',
        'listafp' => 'application/vnd.ibm.modcap',
        'lma' => 'audio/nspaudio',
        'lnk' => 'application/x-ms-shortcut',
        'log' => 'text/plain',
        'lostxml' => 'application/lost+xml',
        'lrf' => 'application/octet-stream',
        'lrm' => 'application/vnd.ms-lrm',
        'lsp' => 'application/x-lisp',
        'lst' => 'text/plain',
        'lsx' => 'text/x-la-asf',
        'ltf' => 'application/vnd.frogans.ltf',
        'ltx' => 'application/x-latex',
        'lua' => 'text/x-lua',
        'luac' => 'application/x-lua-bytecode',
        'lvp' => 'audio/vnd.lucent.voice',
        'lwp' => 'application/vnd.lotus-wordpro',
        'lzh' => 'application/octet-stream',
        'lzx' => 'application/lzx',
        'm' => 'text/plain',
        'm13' => 'application/x-msmediaview',
        'm14' => 'application/x-msmediaview',
        'm1v' => 'video/mpeg',
        'm21' => 'application/mp21',
        'm2a' => 'audio/mpeg',
        'm2v' => 'video/mpeg',
        'm3a' => 'audio/mpeg',
        'm3u' => 'audio/x-mpegurl',
        'm3u8' => 'application/x-mpegURL',
        'm4a' => 'audio/mp4',
        'm4p' => 'application/mp4',
        'm4u' => 'video/vnd.mpegurl',
        'm4v' => 'video/x-m4v',
        'ma' => 'application/mathematica',
        'mads' => 'application/mads+xml',
        'mag' => 'application/vnd.ecowin.chart',
        'maker' => 'application/vnd.framemaker',
        'man' => 'text/troff',
        'manifest' => 'text/cache-manifest',
        'map' => 'application/x-navimap',
        'mar' => 'application/octet-stream',
        'markdown' => 'text/x-markdown',
        'mathml' => 'application/mathml+xml',
        'mb' => 'application/mathematica',
        'mbd' => 'application/mbedlet',
        'mbk' => 'application/vnd.mobius.mbk',
        'mbox' => 'application/mbox',
        'mc' => 'application/x-magic-cap-package-1.0',
        'mc1' => 'application/vnd.medcalcdata',
        'mcd' => 'application/mcad',
        'mcf' => 'image/vasa',
        'mcp' => 'application/netmc',
        'mcurl' => 'text/vnd.curl.mcurl',
        'md' => 'text/x-markdown',
        'mdb' => 'application/x-msaccess',
        'mdi' => 'image/vnd.ms-modi',
        'me' => 'text/troff',
        'mesh' => 'model/mesh',
        'meta4' => 'application/metalink4+xml',
        'metalink' => 'application/metalink+xml',
        'mets' => 'application/mets+xml',
        'mfm' => 'application/vnd.mfmp',
        'mft' => 'application/rpki-manifest',
        'mgp' => 'application/vnd.osgeo.mapguide.package',
        'mgz' => 'application/vnd.proteus.magazine',
        'mht' => 'message/rfc822',
        'mhtml' => 'message/rfc822',
        'mid' => 'application/x-midi',
        'midi' => 'application/x-midi',
        'mie' => 'application/x-mie',
        'mif' => 'application/x-frame',
        'mime' => 'message/rfc822',
        'mj2' => 'video/mj2',
        'mjf' => 'audio/x-vnd.audioexplosion.mjuicemediafile',
        'mjp2' => 'video/mj2',
        'mjpg' => 'video/x-motion-jpeg',
        'mk3d' => 'video/x-matroska',
        'mka' => 'audio/x-matroska',
        'mkd' => 'text/x-markdown',
        'mks' => 'video/x-matroska',
        'mkv' => 'video/x-matroska',
        'mlp' => 'application/vnd.dolby.mlp',
        'mm' => 'application/base64',
        'mmd' => 'application/vnd.chipnuts.karaoke-mmd',
        'mme' => 'application/base64',
        'mmf' => 'application/vnd.smaf',
        'mmr' => 'image/vnd.fujixerox.edmics-mmr',
        'mng' => 'video/x-mng',
        'mny' => 'application/x-msmoney',
        'mobi' => 'application/x-mobipocket-ebook',
        'mod' => 'audio/mod',
        'mods' => 'application/mods+xml',
        'moov' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mp2' => 'audio/mpeg',
        'mp21' => 'application/mp21',
        'mp2a' => 'audio/mpeg',
        'mp3' => 'audio/mpeg3',
        'mp4' => 'video/mp4',
        'mp4a' => 'audio/mp4',
        'mp4s' => 'application/mp4',
        'mp4v' => 'video/mp4',
        'mpa' => 'audio/mpeg',
        'mpc' => 'application/vnd.mophun.certificate',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'audio/mpeg',
        'mpg4' => 'video/mp4',
        'mpga' => 'audio/mpeg',
        'mpkg' => 'application/vnd.apple.installer+xml',
        'mpm' => 'application/vnd.blueice.multipass',
        'mpn' => 'application/vnd.mophun.application',
        'mpp' => 'application/vnd.ms-project',
        'mpt' => 'application/vnd.ms-project',
        'mpv' => 'application/x-project',
        'mpx' => 'application/x-project',
        'mpy' => 'application/vnd.ibm.minipay',
        'mqy' => 'application/vnd.mobius.mqy',
        'mrc' => 'application/marc',
        'mrcx' => 'application/marcxml+xml',
        'ms' => 'text/troff',
        'mscml' => 'application/mediaservercontrol+xml',
        'mseed' => 'application/vnd.fdsn.mseed',
        'mseq' => 'application/vnd.mseq',
        'msf' => 'application/vnd.epson.msf',
        'msh' => 'model/mesh',
        'msi' => 'application/x-msdownload',
        'msl' => 'application/vnd.mobius.msl',
        'msty' => 'application/vnd.muvee.style',
        'mts' => 'model/vnd.mts',
        'mus' => 'application/vnd.musician',
        'musicxml' => 'application/vnd.recordare.musicxml+xml',
        'mv' => 'video/x-sgi-movie',
        'mvb' => 'application/x-msmediaview',
        'mwf' => 'application/vnd.mfer',
        'mxf' => 'application/mxf',
        'mxl' => 'application/vnd.recordare.musicxml',
        'mxml' => 'application/xv+xml',
        'mxs' => 'application/vnd.triscape.mxs',
        'mxu' => 'video/vnd.mpegurl',
        'my' => 'audio/make',
        'mzz' => 'application/x-vnd.audioexplosion.mzz',
        'n-gage' => 'application/vnd.nokia.n-gage.symbian.install',
        'n3' => 'text/n3',
        'nap' => 'image/naplps',
        'naplps' => 'image/naplps',
        'nb' => 'application/mathematica',
        'nbp' => 'application/vnd.wolfram.player',
        'nc' => 'application/x-netcdf',
        'ncm' => 'application/vnd.nokia.configuration-message',
        'ncx' => 'application/x-dtbncx+xml',
        'nfo' => 'text/x-nfo',
        'ngdat' => 'application/vnd.nokia.n-gage.data',
        'nif' => 'image/x-niff',
        'niff' => 'image/x-niff',
        'nitf' => 'application/vnd.nitf',
        'nix' => 'application/x-mix-transfer',
        'nlu' => 'application/vnd.neurolanguage.nlu',
        'nml' => 'application/vnd.enliven',
        'nnd' => 'application/vnd.noblenet-directory',
        'nns' => 'application/vnd.noblenet-sealer',
        'nnw' => 'application/vnd.noblenet-web',
        'npx' => 'image/vnd.net-fpx',
        'nsc' => 'application/x-conference',
        'nsf' => 'application/vnd.lotus-notes',
        'ntf' => 'application/vnd.nitf',
        'nvd' => 'application/x-navidoc',
        'nws' => 'message/rfc822',
        'nzb' => 'application/x-nzb',
        'o' => 'application/octet-stream',
        'oa2' => 'application/vnd.fujitsu.oasys2',
        'oa3' => 'application/vnd.fujitsu.oasys3',
        'oas' => 'application/vnd.fujitsu.oasys',
        'obd' => 'application/x-msbinder',
        'obj' => 'application/x-tgif',
        'oda' => 'application/oda',
        'odb' => 'application/vnd.oasis.opendocument.database',
        'odc' => 'application/vnd.oasis.opendocument.chart',
        'odf' => 'application/vnd.oasis.opendocument.formula',
        'odft' => 'application/vnd.oasis.opendocument.formula-template',
        'odg' => 'application/vnd.oasis.opendocument.graphics',
        'odi' => 'application/vnd.oasis.opendocument.image',
        'odm' => 'application/vnd.oasis.opendocument.text-master',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'oga' => 'audio/ogg',
        'ogg' => 'audio/ogg',
        'ogv' => 'video/ogg',
        'ogx' => 'application/ogg',
        'omc' => 'application/x-omc',
        'omcd' => 'application/x-omcdatamaker',
        'omcr' => 'application/x-omcregerator',
        'omdoc' => 'application/omdoc+xml',
        'onepkg' => 'application/onenote',
        'onetmp' => 'application/onenote',
        'onetoc' => 'application/onenote',
        'onetoc2' => 'application/onenote',
        'opf' => 'application/oebps-package+xml',
        'opml' => 'text/x-opml',
        'oprc' => 'application/vnd.palm',
        'org' => 'application/vnd.lotus-organizer',
        'osf' => 'application/vnd.yamaha.openscoreformat',
        'osfpvg' => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
        'otc' => 'application/vnd.oasis.opendocument.chart-template',
        'otf' => 'font/opentype',
        'otg' => 'application/vnd.oasis.opendocument.graphics-template',
        'oth' => 'application/vnd.oasis.opendocument.text-web',
        'oti' => 'application/vnd.oasis.opendocument.image-template',
        'otm' => 'application/vnd.oasis.opendocument.text-master',
        'otp' => 'application/vnd.oasis.opendocument.presentation-template',
        'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'ott' => 'application/vnd.oasis.opendocument.text-template',
        'oxps' => 'application/oxps',
        'oxt' => 'application/vnd.openofficeorg.extension',
        'p' => 'text/x-pascal',
        'p10' => 'application/pkcs10',
        'p12' => 'application/pkcs-12',
        'p7a' => 'application/x-pkcs7-signature',
        'p7b' => 'application/x-pkcs7-certificates',
        'p7c' => 'application/pkcs7-mime',
        'p7m' => 'application/pkcs7-mime',
        'p7r' => 'application/x-pkcs7-certreqresp',
        'p7s' => 'application/pkcs7-signature',
        'p8' => 'application/pkcs8',
        'part' => 'application/pro_eng',
        'pas' => 'text/x-pascal',
        'paw' => 'application/vnd.pawaafile',
        'pbd' => 'application/vnd.powerbuilder6',
        'pbm' => 'image/x-portable-bitmap',
        'pcap' => 'application/vnd.tcpdump.pcap',
        'pcf' => 'application/x-font-pcf',
        'pcl' => 'application/vnd.hp-pcl',
        'pclxl' => 'application/vnd.hp-pclxl',
        'pct' => 'image/x-pict',
        'pcurl' => 'application/vnd.curl.pcurl',
        'pcx' => 'image/x-pcx',
        'pdb' => 'application/vnd.palm',
        'pdf' => 'application/pdf',
        'pfa' => 'application/x-font-type1',
        'pfb' => 'application/x-font-type1',
        'pfm' => 'application/x-font-type1',
        'pfr' => 'application/font-tdpfr',
        'pfunk' => 'audio/make',
        'pfx' => 'application/x-pkcs12',
        'pgm' => 'image/x-portable-graymap',
        'pgn' => 'application/x-chess-pgn',
        'pgp' => 'application/pgp-encrypted',
        'php' => 'text/x-php',
        'pic' => 'image/x-pict',
        'pict' => 'image/pict',
        'pkg' => 'application/octet-stream',
        'pki' => 'application/pkixcmp',
        'pkipath' => 'application/pkix-pkipath',
        'pko' => 'application/vnd.ms-pki.pko',
        'pl' => 'text/plain',
        'plb' => 'application/vnd.3gpp.pic-bw-large',
        'plc' => 'application/vnd.mobius.plc',
        'plf' => 'application/vnd.pocketlearn',
        'pls' => 'application/pls+xml',
        'plx' => 'application/x-pixclscript',
        'pm' => 'image/x-xpixmap',
        'pm4' => 'application/x-pagemaker',
        'pm5' => 'application/x-pagemaker',
        'pml' => 'application/vnd.ctc-posml',
        'png' => 'image/png',
        'pnm' => 'application/x-portable-anymap',
        'portpkg' => 'application/vnd.macports.portpkg',
        'pot' => 'application/mspowerpoint',
        'potm' => 'application/vnd.ms-powerpoint.template.macroenabled.12',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'pov' => 'model/x-pov',
        'ppa' => 'application/vnd.ms-powerpoint',
        'ppam' => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
        'ppd' => 'application/vnd.cups-ppd',
        'ppm' => 'image/x-portable-pixmap',
        'pps' => 'application/mspowerpoint',
        'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppt' => 'application/mspowerpoint',
        'pptm' => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ppz' => 'application/mspowerpoint',
        'pqa' => 'application/vnd.palm',
        'prc' => 'application/x-mobipocket-ebook',
        'pre' => 'application/vnd.lotus-freelance',
        'prf' => 'application/pics-rules',
        'prt' => 'application/pro_eng',
        'ps' => 'application/postscript',
        'psb' => 'application/vnd.3gpp.pic-bw-small',
        'psd' => 'image/vnd.adobe.photoshop',
        'psf' => 'application/x-font-linux-psf',
        'pskcxml' => 'application/pskc+xml',
        'ptid' => 'application/vnd.pvi.ptid1',
        'pub' => 'application/x-mspublisher',
        'pvb' => 'application/vnd.3gpp.pic-bw-var',
        'pvu' => 'paleovu/x-pv',
        'pwn' => 'application/vnd.3m.post-it-notes',
        'pwz' => 'application/vnd.ms-powerpoint',
        'py' => 'text/x-script.phyton',
        'pya' => 'audio/vnd.ms-playready.media.pya',
        'pyc' => 'applicaiton/x-bytecode.python',
        'pyo' => 'application/x-python-code',
        'pyv' => 'video/vnd.ms-playready.media.pyv',
        'qam' => 'application/vnd.epson.quickanime',
        'qbo' => 'application/vnd.intu.qbo',
        'qcp' => 'audio/vnd.qcelp',
        'qd3' => 'x-world/x-3dmf',
        'qd3d' => 'x-world/x-3dmf',
        'qfx' => 'application/vnd.intu.qfx',
        'qif' => 'image/x-quicktime',
        'qps' => 'application/vnd.publishare-delta-tree',
        'qt' => 'video/quicktime',
        'qtc' => 'video/x-qtc',
        'qti' => 'image/x-quicktime',
        'qtif' => 'image/x-quicktime',
        'qwd' => 'application/vnd.quark.quarkxpress',
        'qwt' => 'application/vnd.quark.quarkxpress',
        'qxb' => 'application/vnd.quark.quarkxpress',
        'qxd' => 'application/vnd.quark.quarkxpress',
        'qxl' => 'application/vnd.quark.quarkxpress',
        'qxt' => 'application/vnd.quark.quarkxpress',
        'ra' => 'audio/x-pn-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'rar' => 'application/x-rar-compressed',
        'ras' => 'application/x-cmu-raster',
        'rast' => 'image/cmu-raster',
        'rcprofile' => 'application/vnd.ipunplugged.rcprofile',
        'rdf' => 'application/rdf+xml',
        'rdz' => 'application/vnd.data-vision.rdz',
        'rep' => 'application/vnd.businessobjects',
        'res' => 'application/x-dtbresource+xml',
        'rexx' => 'text/x-script.rexx',
        'rf' => 'image/vnd.rn-realflash',
        'rgb' => 'image/x-rgb',
        'rif' => 'application/reginfo+xml',
        'rip' => 'audio/vnd.rip',
        'ris' => 'application/x-research-info-systems',
        'rl' => 'application/resource-lists+xml',
        'rlc' => 'image/vnd.fujixerox.edmics-rlc',
        'rld' => 'application/resource-lists-diff+xml',
        'rm' => 'application/vnd.rn-realmedia',
        'rmi' => 'audio/midi',
        'rmm' => 'audio/x-pn-realaudio',
        'rmp' => 'audio/x-pn-realaudio',
        'rms' => 'application/vnd.jcp.javame.midlet-rms',
        'rmvb' => 'application/vnd.rn-realmedia-vbr',
        'rnc' => 'application/relax-ng-compact-syntax',
        'rng' => 'application/ringing-tones',
        'rnx' => 'application/vnd.rn-realplayer',
        'roa' => 'application/rpki-roa',
        'roff' => 'text/troff',
        'rp' => 'image/vnd.rn-realpix',
        'rp9' => 'application/vnd.cloanto.rp9',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'rpss' => 'application/vnd.nokia.radio-presets',
        'rpst' => 'application/vnd.nokia.radio-preset',
        'rq' => 'application/sparql-query',
        'rs' => 'application/rls-services+xml',
        'rsd' => 'application/rsd+xml',
        'rss' => 'application/rss+xml',
        'rt' => 'text/richtext',
        'rtf' => 'application/rtf',
        'rtx' => 'application/rtf',
        'rv' => 'video/vnd.rn-realvideo',
        's' => 'text/x-asm',
        's3m' => 'audio/s3m',
        'saf' => 'application/vnd.yamaha.smaf-audio',
        'saveme' => 'aapplication/octet-stream',
        'sbk' => 'application/x-tbook',
        'sbml' => 'application/sbml+xml',
        'sc' => 'application/vnd.ibm.secure-container',
        'scd' => 'application/x-msschedule',
        'scm' => 'application/x-lotusscreencam',
        'scq' => 'application/scvp-cv-request',
        'scs' => 'application/scvp-cv-response',
        'scurl' => 'text/vnd.curl.scurl',
        'sda' => 'application/vnd.stardivision.draw',
        'sdc' => 'application/vnd.stardivision.calc',
        'sdd' => 'application/vnd.stardivision.impress',
        'sdkd' => 'application/vnd.solent.sdkm+xml',
        'sdkm' => 'application/vnd.solent.sdkm+xml',
        'sdml' => 'text/plain',
        'sdp' => 'application/sdp',
        'sdr' => 'application/sounder',
        'sdw' => 'application/vnd.stardivision.writer',
        'sea' => 'application/sea',
        'see' => 'application/vnd.seemail',
        'seed' => 'application/vnd.fdsn.seed',
        'sema' => 'application/vnd.sema',
        'semd' => 'application/vnd.semd',
        'semf' => 'application/vnd.semf',
        'ser' => 'application/java-serialized-object',
        'set' => 'application/set',
        'setpay' => 'application/set-payment-initiation',
        'setreg' => 'application/set-registration-initiation',
        'sfd-hdstx' => 'application/vnd.hydrostatix.sof-data',
        'sfs' => 'application/vnd.spotfire.sfs',
        'sfv' => 'text/x-sfv',
        'sgi' => 'image/sgi',
        'sgl' => 'application/vnd.stardivision.writer-global',
        'sgm' => 'text/sgml',
        'sgml' => 'text/sgml',
        'sh' => 'application/x-bsh',
        'shar' => 'application/x-bsh',
        'shf' => 'application/shf+xml',
        'shtml' => 'text/html',
        'si' => 'text/vnd.wap.si',
        'sic' => 'application/vnd.wap.sic',
        'sid' => 'image/x-mrsid-image',
        'sig' => 'application/pgp-signature',
        'sil' => 'audio/silk',
        'silo' => 'model/mesh',
        'sis' => 'application/vnd.symbian.install',
        'sisx' => 'application/vnd.symbian.install',
        'sit' => 'application/x-sit',
        'sitx' => 'application/x-stuffitx',
        'skd' => 'application/vnd.koan',
        'skm' => 'application/vnd.koan',
        'skp' => 'application/vnd.koan',
        'skt' => 'application/vnd.koan',
        'sl' => 'application/x-seelogo',
        'slc' => 'application/vnd.wap.slc',
        'sldm' => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
        'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'slt' => 'application/vnd.epson.salt',
        'sm' => 'application/vnd.stepmania.stepchart',
        'smf' => 'application/vnd.stardivision.math',
        'smi' => 'application/smil+xml',
        'smil' => 'application/smil+xml',
        'smv' => 'video/x-smv',
        'smzip' => 'application/vnd.stepmania.package',
        'snd' => 'audio/basic',
        'snf' => 'application/x-font-snf',
        'so' => 'application/octet-stream',
        'sol' => 'application/solids',
        'spc' => 'application/x-pkcs7-certificates',
        'spf' => 'application/vnd.yamaha.smaf-phrase',
        'spl' => 'application/x-futuresplash',
        'spot' => 'text/vnd.in3d.spot',
        'spp' => 'application/scvp-vp-response',
        'spq' => 'application/scvp-vp-request',
        'spr' => 'application/x-sprite',
        'sprite' => 'application/x-sprite',
        'spx' => 'audio/ogg',
        'sql' => 'application/x-sql',
        'src' => 'application/x-wais-source',
        'srt' => 'application/x-subrip',
        'sru' => 'application/sru+xml',
        'srx' => 'application/sparql-results+xml',
        'ssdl' => 'application/ssdl+xml',
        'sse' => 'application/vnd.kodak-descriptor',
        'ssf' => 'application/vnd.epson.ssf',
        'ssi' => 'text/x-server-parsed-html',
        'ssm' => 'application/streamingmedia',
        'ssml' => 'application/ssml+xml',
        'sst' => 'application/vnd.ms-pki.certstore',
        'st' => 'application/vnd.sailingtracker.track',
        'stc' => 'application/vnd.sun.xml.calc.template',
        'std' => 'application/vnd.sun.xml.draw.template',
        'step' => 'application/step',
        'stf' => 'application/vnd.wt.stf',
        'sti' => 'application/vnd.sun.xml.impress.template',
        'stk' => 'application/hyperstudio',
        'stl' => 'application/sla',
        'stp' => 'application/step',
        'str' => 'application/vnd.pg.format',
        'stw' => 'application/vnd.sun.xml.writer.template',
        'sub' => 'text/vnd.dvb.subtitle',
        'sus' => 'application/vnd.sus-calendar',
        'susp' => 'application/vnd.sus-calendar',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'svc' => 'application/vnd.dvb.service',
        'svd' => 'application/vnd.svd',
        'svf' => 'image/vnd.dwg',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'svr' => 'application/x-world',
        'swa' => 'application/x-director',
        'swf' => 'application/x-shockwave-flash',
        'swi' => 'application/vnd.aristanetworks.swi',
        'sxc' => 'application/vnd.sun.xml.calc',
        'sxd' => 'application/vnd.sun.xml.draw',
        'sxg' => 'application/vnd.sun.xml.writer.global',
        'sxi' => 'application/vnd.sun.xml.impress',
        'sxm' => 'application/vnd.sun.xml.math',
        'sxw' => 'application/vnd.sun.xml.writer',
        't' => 'text/troff',
        't3' => 'application/x-t3vm-image',
        'taglet' => 'application/vnd.mynfc',
        'talk' => 'text/x-speech',
        'tao' => 'application/vnd.tao.intent-module-archive',
        'tar' => 'application/x-tar',
        'tbk' => 'application/toolbook',
        'tcap' => 'application/vnd.3gpp2.tcap',
        'tcl' => 'application/x-tcl',
        'tcsh' => 'text/x-script.tcsh',
        'teacher' => 'application/vnd.smart.teacher',
        'tei' => 'application/tei+xml',
        'teicorpus' => 'application/tei+xml',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'text' => 'application/plain',
        'tfi' => 'application/thraud+xml',
        'tfm' => 'application/x-tex-tfm',
        'tga' => 'image/x-tga',
        'tgz' => 'application/gnutar',
        'thmx' => 'application/vnd.ms-officetheme',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'tmo' => 'application/vnd.tmobile-livetv',
        'torrent' => 'application/x-bittorrent',
        'tpl' => 'application/vnd.groove-tool-template',
        'tpt' => 'application/vnd.trid.tpt',
        'tr' => 'text/troff',
        'tra' => 'application/vnd.trueapp',
        'trm' => 'application/x-msterminal',
        'ts' => 'video/MP2T',
        'tsd' => 'application/timestamped-data',
        'tsi' => 'audio/tsp-audio',
        'tsp' => 'application/dsptype',
        'tsv' => 'text/tab-separated-values',
        'ttc' => 'application/x-font-ttf',
        'ttf' => 'application/x-font-ttf',
        'ttl' => 'text/turtle',
        'turbot' => 'image/florian',
        'twd' => 'application/vnd.simtech-mindmapper',
        'twds' => 'application/vnd.simtech-mindmapper',
        'txd' => 'application/vnd.genomatix.tuxedo',
        'txf' => 'application/vnd.mobius.txf',
        'txt' => 'text/plain',
        'u32' => 'application/x-authorware-bin',
        'udeb' => 'application/x-debian-package',
        'ufd' => 'application/vnd.ufdl',
        'ufdl' => 'application/vnd.ufdl',
        'uil' => 'text/x-uil',
        'ulx' => 'application/x-glulx',
        'umj' => 'application/vnd.umajin',
        'uni' => 'text/uri-list',
        'unis' => 'text/uri-list',
        'unityweb' => 'application/vnd.unity',
        'unv' => 'application/i-deas',
        'uoml' => 'application/vnd.uoml+xml',
        'uri' => 'text/uri-list',
        'uris' => 'text/uri-list',
        'urls' => 'text/uri-list',
        'ustar' => 'application/x-ustar',
        'utz' => 'application/vnd.uiq.theme',
        'uu' => 'application/octet-stream',
        'uue' => 'text/x-uuencode',
        'uva' => 'audio/vnd.dece.audio',
        'uvd' => 'application/vnd.dece.data',
        'uvf' => 'application/vnd.dece.data',
        'uvg' => 'image/vnd.dece.graphic',
        'uvh' => 'video/vnd.dece.hd',
        'uvi' => 'image/vnd.dece.graphic',
        'uvm' => 'video/vnd.dece.mobile',
        'uvp' => 'video/vnd.dece.pd',
        'uvs' => 'video/vnd.dece.sd',
        'uvt' => 'application/vnd.dece.ttml+xml',
        'uvu' => 'video/vnd.uvvu.mp4',
        'uvv' => 'video/vnd.dece.video',
        'uvva' => 'audio/vnd.dece.audio',
        'uvvd' => 'application/vnd.dece.data',
        'uvvf' => 'application/vnd.dece.data',
        'uvvg' => 'image/vnd.dece.graphic',
        'uvvh' => 'video/vnd.dece.hd',
        'uvvi' => 'image/vnd.dece.graphic',
        'uvvm' => 'video/vnd.dece.mobile',
        'uvvp' => 'video/vnd.dece.pd',
        'uvvs' => 'video/vnd.dece.sd',
        'uvvt' => 'application/vnd.dece.ttml+xml',
        'uvvu' => 'video/vnd.uvvu.mp4',
        'uvvv' => 'video/vnd.dece.video',
        'uvvx' => 'application/vnd.dece.unspecified',
        'uvvz' => 'application/vnd.dece.zip',
        'uvx' => 'application/vnd.dece.unspecified',
        'uvz' => 'application/vnd.dece.zip',
        'vcard' => 'text/vcard',
        'vcd' => 'application/x-cdlink',
        'vcf' => 'text/x-vcard',
        'vcg' => 'application/vnd.groove-vcard',
        'vcs' => 'text/x-vcalendar',
        'vcx' => 'application/vnd.vcx',
        'vda' => 'application/vda',
        'vdo' => 'video/vdo',
        'vew' => 'application/groupwise',
        'vis' => 'application/vnd.visionary',
        'viv' => 'video/vivo',
        'vivo' => 'video/vivo',
        'vmd' => 'application/vocaltec-media-desc',
        'vmf' => 'application/vocaltec-media-file',
        'vob' => 'video/x-ms-vob',
        'voc' => 'audio/voc',
        'vor' => 'application/vnd.stardivision.writer',
        'vos' => 'video/vosaic',
        'vox' => 'application/x-authorware-bin',
        'vqe' => 'audio/x-twinvq-plugin',
        'vqf' => 'audio/x-twinvq',
        'vql' => 'audio/x-twinvq-plugin',
        'vrml' => 'application/x-vrml',
        'vrt' => 'x-world/x-vrt',
        'vsd' => 'application/vnd.visio',
        'vsf' => 'application/vnd.vsf',
        'vss' => 'application/vnd.visio',
        'vst' => 'application/vnd.visio',
        'vsw' => 'application/vnd.visio',
        'vtt' => 'text/vtt',
        'vtu' => 'model/vnd.vtu',
        'vxml' => 'application/voicexml+xml',
        'w3d' => 'application/x-director',
        'w60' => 'application/wordperfect6.0',
        'w61' => 'application/wordperfect6.1',
        'w6w' => 'application/msword',
        'wad' => 'application/x-doom',
        'wav' => 'audio/wav',
        'wax' => 'audio/x-ms-wax',
        'wb1' => 'application/x-qpro',
        'wbmp' => 'image/vnd.wap.wbmp',
        'wbs' => 'application/vnd.criticaltools.wbs+xml',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wcm' => 'application/vnd.ms-works',
        'wdb' => 'application/vnd.ms-works',
        'wdp' => 'image/vnd.ms-photo',
        'web' => 'application/vnd.xara',
        'weba' => 'audio/webm',
        'webapp' => 'application/x-web-app-manifest+json',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
        'wg' => 'application/vnd.pmi.widget',
        'wgt' => 'application/widget',
        'wiz' => 'application/msword',
        'wk1' => 'application/x-123',
        'wks' => 'application/vnd.ms-works',
        'wm' => 'video/x-ms-wm',
        'wma' => 'audio/x-ms-wma',
        'wmd' => 'application/x-ms-wmd',
        'wmf' => 'application/x-msmetafile',
        'wml' => 'text/vnd.wap.wml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmls' => 'text/vnd.wap.wmlscript',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'wmv' => 'video/x-ms-wmv',
        'wmx' => 'video/x-ms-wmx',
        'wmz' => 'application/x-msmetafile',
        'woff' => 'application/x-font-woff',
        'word' => 'application/msword',
        'wp' => 'application/wordperfect',
        'wp5' => 'application/wordperfect',
        'wp6' => 'application/wordperfect',
        'wpd' => 'application/wordperfect',
        'wpl' => 'application/vnd.ms-wpl',
        'wps' => 'application/vnd.ms-works',
        'wq1' => 'application/x-lotus',
        'wqd' => 'application/vnd.wqd',
        'wri' => 'application/mswrite',
        'wrl' => 'application/x-world',
        'wrz' => 'model/vrml',
        'wsc' => 'text/scriplet',
        'wsdl' => 'application/wsdl+xml',
        'wspolicy' => 'application/wspolicy+xml',
        'wsrc' => 'application/x-wais-source',
        'wtb' => 'application/vnd.webturbo',
        'wtk' => 'application/x-wintalk',
        'wvx' => 'video/x-ms-wvx',
        'x-png' => 'image/png',
        'x32' => 'application/x-authorware-bin',
        'x3d' => 'model/x3d+xml',
        'x3db' => 'model/x3d+binary',
        'x3dbz' => 'model/x3d+binary',
        'x3dv' => 'model/x3d+vrml',
        'x3dvz' => 'model/x3d+vrml',
        'x3dz' => 'model/x3d+xml',
        'xaml' => 'application/xaml+xml',
        'xap' => 'application/x-silverlight-app',
        'xar' => 'application/vnd.xara',
        'xbap' => 'application/x-ms-xbap',
        'xbd' => 'application/vnd.fujixerox.docuworks.binder',
        'xbm' => 'image/x-xbitmap',
        'xdf' => 'application/xcap-diff+xml',
        'xdm' => 'application/vnd.syncml.dm+xml',
        'xdp' => 'application/vnd.adobe.xdp+xml',
        'xdr' => 'video/x-amt-demorun',
        'xdssc' => 'application/dssc+xml',
        'xdw' => 'application/vnd.fujixerox.docuworks',
        'xenc' => 'application/xenc+xml',
        'xer' => 'application/patch-ops-error+xml',
        'xfdf' => 'application/vnd.adobe.xfdf',
        'xfdl' => 'application/vnd.xfdl',
        'xgz' => 'xgl/drawing',
        'xht' => 'application/xhtml+xml',
        'xhtml' => 'application/xhtml+xml',
        'xhvml' => 'application/xv+xml',
        'xif' => 'image/vnd.xiff',
        'xl' => 'application/excel',
        'xla' => 'application/excel',
        'xlam' => 'application/vnd.ms-excel.addin.macroenabled.12',
        'xlb' => 'application/excel',
        'xlc' => 'application/excel',
        'xld' => 'application/excel',
        'xlf' => 'application/x-xliff+xml',
        'xlk' => 'application/excel',
        'xll' => 'application/excel',
        'xlm' => 'application/excel',
        'xls' => 'application/excel',
        'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
        'xlsm' => 'application/vnd.ms-excel.sheet.macroenabled.12',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlt' => 'application/excel',
        'xltm' => 'application/vnd.ms-excel.template.macroenabled.12',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xlv' => 'application/excel',
        'xlw' => 'application/excel',
        'xm' => 'audio/xm',
        'xml' => 'application/xml',
        'xmz' => 'xgl/movie',
        'xo' => 'application/vnd.olpc-sugar',
        'xop' => 'application/xop+xml',
        'xpdl' => 'application/xml',
        'xpi' => 'application/x-xpinstall',
        'xpix' => 'application/x-vnd.ls-xpix',
        'xpl' => 'application/xproc+xml',
        'xpm' => 'image/x-xpixmap',
        'xpr' => 'application/vnd.is-xpr',
        'xps' => 'application/vnd.ms-xpsdocument',
        'xpw' => 'application/vnd.intercon.formnet',
        'xpx' => 'application/vnd.intercon.formnet',
        'xsl' => 'application/xml',
        'xslt' => 'application/xslt+xml',
        'xsm' => 'application/vnd.syncml+xml',
        'xspf' => 'application/xspf+xml',
        'xsr' => 'video/x-amt-showrun',
        'xul' => 'application/vnd.mozilla.xul+xml',
        'xvm' => 'application/xv+xml',
        'xvml' => 'application/xv+xml',
        'xwd' => 'image/x-xwd',
        'xyz' => 'chemical/x-xyz',
        'xz' => 'application/x-xz',
        'yang' => 'application/yang',
        'yin' => 'application/yin+xml',
        'z' => 'application/x-compress',
        'z1' => 'application/x-zmachine',
        'z2' => 'application/x-zmachine',
        'z3' => 'application/x-zmachine',
        'z4' => 'application/x-zmachine',
        'z5' => 'application/x-zmachine',
        'z6' => 'application/x-zmachine',
        'z7' => 'application/x-zmachine',
        'z8' => 'application/x-zmachine',
        'zaz' => 'application/vnd.zzazz.deck+xml',
        'zip' => 'application/x-compressed',
        'zir' => 'application/vnd.zul',
        'zirz' => 'application/vnd.zul',
        'zmm' => 'application/vnd.handheld-entertainment+xml',
        'zoo' => 'application/octet-stream',
        'zsh' => 'text/x-script.zsh',
        '123' => 'application/vnd.lotus-1-2-3'
    );

    /**
     * Store uploaded files in this folder
     * @var string
     */
    public $uploadsDir = "uploads/";
    
    /**
     * Store uploaded images in this folder
     * @var string
     */
    public $imagesDir = "uploads/images/";
    
    /**
     * For image files, store generated thumbnails in this folder
     * @var string
     */
    public $thumbsDir  = "uploads/images/thumbs/";
    
    /**
     * Maximum file size for the uploaded files
     * @var int
     */
    public $maxFileSize = 3000000; // 3Mb
    
    /**
     * Flag for thumbnail generation
     * @var bool
     */
    public $makeThumbs = true;
    
    /**
     * Generated thumbnails width
     * @var int
     */
    public $thumbWidth = 120;
    
    /**
     * Generated thumbnails height
     * @var int
     */
    public $thumbHeight = 100;
    
    /**
     * Flag to keep the aspect ratio (width / height ) for the generated thumbnails
     * @var bool
     */
    public $keepAspectRatio = true;
    
    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
        
        $this->setRandomPrefix();
    }
    
    /**
     * Get the extension of a given file
     * @param string $path The path of the file to be processed
     * @return string
     */
    public function getExtension(string $path): string{
        $ext = "";
        if (strpos($path, ".") !== false){
            $parts = explode(".", $path);
            $ext = strtolower(end($parts));
        }
        return $ext;
    }
    
    /**
     * Get the mime type of a given file
     * @param string $path The path of the file to be processed
     * @return string
     */
    public function getMimeType(string $path): string {

        /**
         * check for known file extensions
         */
        $ext = $this->getExtension($path);
        if ($ext){
            if (isset($this->mimeTypes[$ext])) {
                return $this->mimeTypes[$ext];
            }
        }
        
        /**
         * if the file doesn't have a known extension (or no extension at all),
         * force it to something that can be automatically downloaded
         */
        $ret = "application/octet-stream";
        
        if (function_exists("finfo_open")){
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $ret = finfo_file($finfo, $path);
            finfo_close($finfo);
        }

        return $ret;
    }

    /**
     * Check if a given file is image or not
     * @param string $path The path of the file to be processed
     * @return bool
     */
    public function isImage(string $path): bool{
        return in_array($this->getExtension($path), array("jpg", "jpeg", "png", "gif", "bmp", "wbmp", "xbmp", "ico"));
    }
    
    /**
     * CHeck if a given file can have a thumbnail or not
     * @param string $path The path of the file to be processed
     * @return bool
     */
    public function canHaveThumbnail(string $path): bool{
        if (!$this->isImage($path)) return false;
        return in_array($this->getExtension($path), array("jpg", "jpeg", "png", "gif"));
    }
    
    /**
     * Generate thumbnail for a given image, based on the input arguments
     * @param string $path The path of the file to be processed
     * @param int $width The desired thumbnail width
     * @param int $height The desired thumbnail height
     * @param bool $keepAspectRatio If set to true, the resulting height may differ from the desired one
     * @return string
     */
    public function generateThumbnail(string $path, int $width=400, int $height=300, bool $keepAspectRatio=true): string{
        $thumbName = $this->getThumbName($path, $width, $height, $keepAspectRatio);
        if (!$this->canHaveThumbnail($path)) return false;
        $ext = $this->getExtension($path);
        switch ($ext){
            case "jpg":
            case "jpeg":
                $createFunc = "imagecreatefromjpeg";
                $saveFunc = "imagejpeg";
                break;
            case "png":
                $createFunc = "imagecreatefrompng";
                $saveFunc = "imagepng";
                break;
            case "gif":
                $createFunc = "imagecreatefromgif";
                $saveFunc = "imagegif";
                break;
        }
        
        if (!function_exists($createFunc) || !function_exists($saveFunc)) return false;
        $img = $createFunc($path);
        if (!$img) return "";
        
        $originalWidth = imagesx($img);
        $originalHeight = imagesy($img);
        if ($keepAspectRatio){
            $ratio = $originalWidth / $originalHeight;
            $height = $width / $ratio;
        }
        
        $destImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($destImage, $img, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
        
        $ret = $saveFunc($destImage, $thumbName);
        return ($ret) ? $thumbName : "";
    }
    
    /**
     * Generate a random string of a given length
     * @param int $length The length of the resulting string
     * @return string
     */
    private function randomString(int $length = 10): string{
        $ret = "";
        $alphabet = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $max = mb_strlen($alphabet, '8bit') - 1;
        for ($i = 0; $i < $length; $i++){
            $ret .= $alphabet[rand(0, $max)];
        }
        return $ret;
    }
    
    /**
     * Set a random prefix for the generated thumbnail folders
     * @return \CuxFramework\components\uploader\CuxUploader
     */
    private function setRandomPrefix(): CuxUploader{
        $this->_prefix = microtime().$this->randomString(5)."_";
        return $this;
    }
    
    /**
     * Setter for the $_prefix property
     * @param string $prefix
     * @return \CuxFramework\components\uploader\CuxUploader
     */
    public function setPrefix(string $prefix = ""): CuxUploader{
        $this->_prefix = $prefix;
        return $this;
    }
    
    /**
     * Get the thumbnail path for a given image file, based on given arguments
     * @param string $path The path of the file to be processed
     * @param int $width The desired thumbnail width
     * @param int $height The desired thumbnail height
     * @param bool $keepAspectRatio If set to true, the resulting height may differ from the desired one
     * @return string
     * @throws \Exception
     */
    public function getThumbName(string $path, int $width=400, int $height=300, bool $keepAspectRatio=true): string{
        if (!$this->isImage($path)) return false;
        if (!file_exists($this->uploadsDir)){
            if (!@mkdir($this->uploadsDir)){
                throw new \Exception(Cux::translate("core.errors", "Cannot create the uploads directory", array(), "Error shown while uploading a file"), 505);
            }
        }
        if (!file_exists($this->imagesDir)){
            if (!@mkdir($this->imagesDir)){
                throw new \Exception(Cux::translate("core.errors", "Cannot create the uploaded images directory", array(), "Error shown while uploading a file"), 505);
            }
        }
        if (!file_exists($this->thumbsDir)){
            if (@mkdir($this->thumbsDir)){
                throw new \Exception(Cux::translate("core.errors", "Cannot create the uploaded images thumbnails directory", array(), "Error shown while uploading a file"), 505);
            }
        }
        $thumbDir = $this->thumbsDir."{$width}_{$height}";
        if (!file_exists($thumbDir)){
            if (!@mkdir($thumbDir)){
                throw new \Exception(Cux::translate("core.errors", "Cannot create the uploaded images thumbnails directory", array(), "Error shown while uploading a file"), 505);
            }
        }
//        return $thumbDir.DIRECTORY_SEPARATOR.$this->_prefix.$width."_".$height."_".(($keepAspectRatio) ? "yes_" : "no_").basename($path);
        return $thumbDir.DIRECTORY_SEPARATOR.$width."_".$height."_".(($keepAspectRatio) ? "yes_" : "no_").basename($path);
    }
    
    /**
     * Get/generate a thumbnail for a given image file, based on given arguments
     * @param string $path The path of the file to be processed
     * @param int $width The desired thumbnail width
     * @param int $height The desired thumbnail height
     * @param bool $keepAspectRatio If set to true, the resulting height may differ from the desired one
     * @return string
     */
    public function getThumbnail(string $path, int $width=400, int $height=300, bool $keepAspectRatio=true): string{
        if (!$this->isImage($path)) return false;
        $thumbName = $this->getThumbName($path, $width, $height, $keepAspectRatio);
        if (file_exists($thumbName)){
            return $thumbName;
        }
        return $this->generateThumbnail($path, $width, $height, $keepAspectRatio);
    }
    
    /**
     * Convert bytes size to human-readable values, reported in Bytes, KiloBytes, MegaBytes....
     * @param int $size The size in bytes
     * @return string
     */
    public function convert(int $size): string {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return sprintf("%.2f %s", $size / pow(1024, ($i = floor(log($size, 1024)))), ' ' . $unit[$i]);
    }
    
    /**
     * Store a given uploaded file and generate thumbnails, if required
     * @param string $name The name of the uploaded file
     * @return array
     * @throws \Exception
     */
    public function uploadFile(string $name): array{
        if (!isset($_FILES[$name])){
            throw new \Exception(Cux::translate("core.errors", "No file found", array(), "Error shown while uploading a file"), 505);
        }
        if ($_FILES[$name]["size"] > $this->maxFileSize){
            $fileSize = $this->convert($this->maxFileSize);
            throw new \Exception(Cux::translate("core.errors", "File limit exceeded! Max allowed file size: {max_file_size}", array("{file_size}" => $fileSize), "Error shown while uploading a file"), 505);
        }
        switch ($_FILES[$name]['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \Exception(Cux::translate("core.errors", "No file found"), array(), "Error shown while uploading a file", 505);
            case UPLOAD_ERR_INI_SIZE:
                throw new \Exception(Cux::translate("core.errors", "Config max file size exceeded", array(), "Error shown while uploading a file"), 505);
            case UPLOAD_ERR_FORM_SIZE:
                throw new \Exception(Cux::translate("core.errors", "Form max file size exceeded", array(), "Error shown while uploading a file"), 505);
            default:
                throw new \Exception(Cux::translate("core.errors", "Unknown upload error", array(), "Error shown while uploading a file"), 504);
        }
        $tmpName = $_FILES[$name]['tmp_name'];
        $realName = $_FILES[$name]['name'];
        $path = ($this->isImage($realName) ? $this->imagesDir : $this->uploadsDir).sha1_file($tmpName).".".$this->getExtension($realName);
        if (!@move_uploaded_file($tmpName, $path)){
            throw new \Exception(Cux::translate("core.errors", "Uploaded file cannot be saved", array(), "Error shown while uploading a file"), 504);
        }
        
        $ret = array(
            "name" => $realName,
            "path" => $path,
            "type" => $this->getMimeType($path),
            "size" => $this->convert($_FILES[$name]["size"])
        );
        
        if ($this->makeThumbs && $this->isImage($realName)){
            $ret["thumb_path"] = $this->generateThumbnail($path, $this->thumbWidth, $this->thumbHeight, $this->keepAspectRatio);
        }
        
        return $ret;
    }
    
}
