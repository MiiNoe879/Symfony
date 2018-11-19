<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$site = LANGUAGE_ID == 'de'
	? 'http://www.thurlyos.de'
	: (\Thurly\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID) == 'ru'
		? 'http://www.thurlyos.ru'
		: 'http://www.thurlyos.com'
	);
?>


									</td>
								</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<tr>
						<td height="8" bgcolor="#ffffff" width="6" style="background:url(<?=$site?>/mailimg/new/bl.png)"></td>
						<td height="8" bgcolor="#ffffff"></td>
						<td height="8" bgcolor="#ffffff" width="6" style="background:url(<?=$site?>/mailimg/new/br.png)"></td>
					</tr>
					<tr>
						<td width="700" colspan="3" height="20"></td>
					</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td bgcolor="#f5f8f9" align="center" style="background:#f5f8f9;" height="100">
<?
if(LANGUAGE_ID == 'de'):
?>

				<table>
					<tr>
						<td width="28px">
							<a target="_blank" href="http://www.facebook.com/thurlyosde"><img alt="Facebook" src="http://thurlyos.com/thurly/templates/b24/img/icoFacebook.png"></a>
						</td>
						<td width="2px"></td>
						<td width="28px">
							<a target="_blank" href="http://twitter.com/thurlyosde"><img alt="Twitter" src="http://thurlyos.com/thurly/templates/b24/img/icoTwitter.png"></a>
						</td>
						<td width="2px"></td>
						<td width="28px">
							<a target="_blank" href="http://www.xing.com/net/thurly"><img alt="Xing" src="http://thurlyos.com/thurly/templates/b24/img/icoXing.png"></a>
						</td>
						<td width="2px"></td>
						<td width="28px">
							<a target="_blank" href="https://plus.google.com/+ThurlyOSDeu/videos"><img alt="Google+" src="http://thurlyos.com/thurly/templates/b24/img/icoGoogle.png"></a>
						</td>
						<td width="2px"></td>
						<td width="28px">
							<a target="_blank" href="http://www.youtube.com/channel/UC8G6EN8RSb3N_FRO8oFjDMQ"><img alt="YouTube" src="http://thurlyos.com/thurly/templates/b24/img/icoYoutube.png"></a>
						</td>
					</tr>
					<tr>
						<td colspan="9" align="center">
							<font color="#575757" face="Calibri" style="font-size:13px;"><?=\Thurly\Main\Localization\Loc::getMessage("B24_MAIL_JOIN_COPY")?></font>
						</td>
					</tr>
				</table>

<?
elseif(\Thurly\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID) == 'en'):
?>

				<table>
					<tbody>
					<tr>
						<td width="28px">
							<a target="_blank" href="http://www.facebook.com/thurlyos"><img alt="Facebook" src="http://thurlyos.com/thurly/templates/b24/img/icoFacebook.png"></a>
						</td>
						<td width="2px">
						</td>
						<td width="28px">
							<a target="_blank" href="http://twitter.com/thurlyos"><img alt="Twitter" src="http://thurlyos.com/thurly/templates/b24/img/icoTwitter.png"></a>
						</td>
						<td width="2px">
						</td>
						<td width="28px">
							<a target="_blank" href="http://www.linkedin.com/groups/ThurlyOS-4426654"><img alt="LinkedIn" src="http://thurlyos.com/thurly/templates/b24/img/icoLinkedin.png"></a>
						</td>
						<td width="2px">
						</td>
						<td width="28px">
							<a target="_blank" href="https://plus.google.com/101157989654968484902"><img alt="Google+" src="http://thurlyos.com/thurly/templates/b24/img/icoGoogle.png"></a>
						</td>
						<td width="2px">
						</td>
						<td width="28px">
							<a target="_blank" href="http://www.youtube.com/thurlyos"><img alt="YouTube" src="http://thurlyos.com/thurly/templates/b24/img/icoYoutube.png"></a>
						</td>
					</tr>
					<tr>
						<td colspan="9" align="center">
							<span style="font-size: 13px; font-family: Calibri; color: #575757;"><?=\Thurly\Main\Localization\Loc::getMessage("B24_MAIL_JOIN_COPY")?></span>
						</td>
					</tr>
					</tbody>
				</table>

<?
else:
?>

				<table>
					<tbody>
					<tr>
						<td>
							<?=\Thurly\Main\Localization\Loc::getMessage("B24_MAIL_JOIN_COPY")?>
						</td>
					</tr>
					</tbody>
				</table>

<?
endif;
?>
			</td>
		</tr>
		</tbody>
	</table>
</div>

</body>
</html>