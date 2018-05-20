<?php
# Copyright (c) 2015, phpfmt and its authors
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
#
# 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
#
# 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
#
# 3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

namespace {
	require_once __DIR__ . '/constants.php';
}

namespace Extern {
	require FMT_VENDOR_DIR . '/symfony/console/Formatter/OutputFormatterInterface.php';
	require FMT_VENDOR_DIR . '/symfony/console/Helper/HelperInterface.php';
	require FMT_VENDOR_DIR . '/symfony/console/Helper/Helper.php';
	require FMT_VENDOR_DIR . '/symfony/console/Formatter/OutputFormatterStyleStack.php';
	require FMT_VENDOR_DIR . '/symfony/console/Formatter/OutputFormatterStyleInterface.php';
	require FMT_VENDOR_DIR . '/symfony/console/Formatter/OutputFormatterStyle.php';
	require FMT_VENDOR_DIR . '/symfony/console/Formatter/OutputFormatter.php';
	require FMT_VENDOR_DIR . '/symfony/console/Output/OutputInterface.php';
	require FMT_VENDOR_DIR . '/symfony/console/Output/ConsoleOutputInterface.php';
	require FMT_VENDOR_DIR . '/symfony/console/Output/Output.php';
	require FMT_VENDOR_DIR . '/symfony/console/Output/StreamOutput.php';
	require FMT_VENDOR_DIR . '/symfony/console/Output/ConsoleOutput.php';
	require FMT_VENDOR_DIR . '/symfony/console/Helper/ProgressBar.php';
}

namespace {
	$concurrent = function_exists('pcntl_fork');
	if ($concurrent) {
		require FMT_VENDOR_DIR . '/dericofilho/csp/csp.php';
	}
	require FMT_SRC_DIR . '/Core/Cacher.php';
	$enableCache = false;
	if (class_exists('SQLite3')) {
		$enableCache = true;
		require FMT_SRC_DIR . '/Core/Cache.php';
	} else {
		require FMT_SRC_DIR . '/Core/Cache_dummy.php';
	}

	require FMT_SRC_DIR . '/version.php';
	require FMT_SRC_DIR . '/helpers.php';
	require FMT_SRC_DIR . '/selfupdate.php';

	require FMT_SRC_DIR . '/Core/FormatterPass.php';
	require FMT_SRC_DIR . '/Additionals/AdditionalPass.php';
	require FMT_SRC_DIR . '/Core/BaseCodeFormatter.php';
	if ('1' === getenv('FMTDEBUG') || 'step' === getenv('FMTDEBUG')) {
		require FMT_SRC_DIR . '/Core/CodeFormatter_debug.php';
	} elseif ('profile' === getenv('FMTDEBUG')) {
		require FMT_SRC_DIR . '/Core/CodeFormatter_profile.php';
	} else {
		require FMT_SRC_DIR . '/Core/CodeFormatter.php';
	}

	require FMT_SRC_DIR . '/Core/AddMissingCurlyBraces.php';
	require FMT_SRC_DIR . '/Core/AutoImport.php';
	require FMT_SRC_DIR . '/Core/ConstructorPass.php';
	require FMT_SRC_DIR . '/Core/EliminateDuplicatedEmptyLines.php';
	require FMT_SRC_DIR . '/Core/ExternalPass.php';
	require FMT_SRC_DIR . '/Core/ExtraCommaInArray.php';
	require FMT_SRC_DIR . '/Core/LeftAlignComment.php';
	require FMT_SRC_DIR . '/Core/MergeCurlyCloseAndDoWhile.php';
	require FMT_SRC_DIR . '/Core/MergeDoubleArrowAndArray.php';
	require FMT_SRC_DIR . '/Core/MergeParenCloseWithCurlyOpen.php';
	require FMT_SRC_DIR . '/Core/NormalizeIsNotEquals.php';
	require FMT_SRC_DIR . '/Core/NormalizeLnAndLtrimLines.php';
	require FMT_SRC_DIR . '/Core/Reindent.php';
	require FMT_SRC_DIR . '/Core/ReindentColonBlocks.php';
	require FMT_SRC_DIR . '/Core/ReindentComments.php';
	require FMT_SRC_DIR . '/Core/ReindentEqual.php';
	require FMT_SRC_DIR . '/Core/ReindentObjOps.php';
	require FMT_SRC_DIR . '/Core/ResizeSpaces.php';
	require FMT_SRC_DIR . '/Core/RTrim.php';
	require FMT_SRC_DIR . '/Core/SettersAndGettersPass.php';
	require FMT_SRC_DIR . '/Core/SplitCurlyCloseAndTokens.php';
	require FMT_SRC_DIR . '/Core/StripExtraCommaInList.php';
	require FMT_SRC_DIR . '/Core/SurrogateToken.php';
	require FMT_SRC_DIR . '/Core/TwoCommandsInSameLine.php';

	require FMT_SRC_DIR . '/PSR/PSR1BOMMark.php';
	require FMT_SRC_DIR . '/PSR/PSR1ClassConstants.php';
	require FMT_SRC_DIR . '/PSR/PSR1ClassNames.php';
	require FMT_SRC_DIR . '/PSR/PSR1MethodNames.php';
	require FMT_SRC_DIR . '/PSR/PSR1OpenTags.php';
	require FMT_SRC_DIR . '/PSR/PSR2AlignObjOp.php';
	require FMT_SRC_DIR . '/PSR/PSR2CurlyOpenNextLine.php';
	require FMT_SRC_DIR . '/PSR/PSR2IndentWithSpace.php';
	require FMT_SRC_DIR . '/PSR/PSR2KeywordsLowerCase.php';
	require FMT_SRC_DIR . '/PSR/PSR2LnAfterNamespace.php';
	require FMT_SRC_DIR . '/PSR/PSR2ModifierVisibilityStaticOrder.php';
	require FMT_SRC_DIR . '/PSR/PSR2SingleEmptyLineAndStripClosingTag.php';
	require FMT_SRC_DIR . '/PSR/PsrDecorator.php';

	require FMT_SRC_DIR . '/Additionals/AddMissingParentheses.php';
	require FMT_SRC_DIR . '/Additionals/AliasToMaster.php';
	require FMT_SRC_DIR . '/Additionals/AlignConstVisibilityEquals.php';
	require FMT_SRC_DIR . '/Additionals/AlignDoubleArrow.php';
	require FMT_SRC_DIR . '/Additionals/AlignDoubleSlashComments.php';
	require FMT_SRC_DIR . '/Additionals/AlignEquals.php';
	require FMT_SRC_DIR . '/Additionals/AlignGroupDoubleArrow.php';
	require FMT_SRC_DIR . '/Additionals/AlignPHPCode.php';
	require FMT_SRC_DIR . '/Additionals/AlignTypehint.php';
	require FMT_SRC_DIR . '/Additionals/AllmanStyleBraces.php';
	require FMT_SRC_DIR . '/Additionals/AutoPreincrement.php';
	require FMT_SRC_DIR . '/Additionals/AutoSemicolon.php';
	require FMT_SRC_DIR . '/Additionals/CakePHPStyle.php';
	require FMT_SRC_DIR . '/Additionals/ClassToSelf.php';
	require FMT_SRC_DIR . '/Additionals/ClassToStatic.php';
	require FMT_SRC_DIR . '/Additionals/ConvertOpenTagWithEcho.php';
	require FMT_SRC_DIR . '/Additionals/DocBlockToComment.php';
	require FMT_SRC_DIR . '/Additionals/DoubleToSingleQuote.php';
	require FMT_SRC_DIR . '/Additionals/EchoToPrint.php';
	require FMT_SRC_DIR . '/Additionals/EncapsulateNamespaces.php';
	require FMT_SRC_DIR . '/Additionals/GeneratePHPDoc.php';
	require FMT_SRC_DIR . '/Additionals/IndentTernaryConditions.php';
	require FMT_SRC_DIR . '/Additionals/JoinToImplode.php';
	require FMT_SRC_DIR . '/Additionals/LeftWordWrap.php';
	require FMT_SRC_DIR . '/Additionals/LongArray.php';
	require FMT_SRC_DIR . '/Additionals/MergeElseIf.php';
	require FMT_SRC_DIR . '/Additionals/SplitElseIf.php';
	require FMT_SRC_DIR . '/Additionals/MergeNamespaceWithOpenTag.php';
	require FMT_SRC_DIR . '/Additionals/MildAutoPreincrement.php';
	require FMT_SRC_DIR . '/Additionals/NewLineBeforeReturn.php';
	require FMT_SRC_DIR . '/Additionals/NoSpaceAfterPHPDocBlocks.php';
	require FMT_SRC_DIR . '/Additionals/OrganizeClass.php';
	require FMT_SRC_DIR . '/Additionals/OrderAndRemoveUseClauses.php';
	require FMT_SRC_DIR . '/Additionals/OnlyOrderUseClauses.php';
	require FMT_SRC_DIR . '/Additionals/OrderMethod.php';
	require FMT_SRC_DIR . '/Additionals/OrderMethodAndVisibility.php';
	require FMT_SRC_DIR . '/Additionals/PHPDocTypesToFunctionTypehint.php';
	require FMT_SRC_DIR . '/Additionals/PrettyPrintDocBlocks.php';
	require FMT_SRC_DIR . '/Additionals/PSR2EmptyFunction.php';
	require FMT_SRC_DIR . '/Additionals/PSR2MultilineFunctionParams.php';
	require FMT_SRC_DIR . '/Additionals/ReindentAndAlignObjOps.php';
	require FMT_SRC_DIR . '/Additionals/ReindentSwitchBlocks.php';
	require FMT_SRC_DIR . '/Additionals/RemoveIncludeParentheses.php';
	require FMT_SRC_DIR . '/Additionals/RemoveSemicolonAfterCurly.php';
	require FMT_SRC_DIR . '/Additionals/RemoveUseLeadingSlash.php';
	require FMT_SRC_DIR . '/Additionals/ReplaceBooleanAndOr.php';
	require FMT_SRC_DIR . '/Additionals/ReplaceIsNull.php';
	require FMT_SRC_DIR . '/Additionals/RestoreComments.php';
	require FMT_SRC_DIR . '/Additionals/ReturnNull.php';
	require FMT_SRC_DIR . '/Additionals/ShortArray.php';
	require FMT_SRC_DIR . '/Additionals/SmartLnAfterCurlyOpen.php';
	require FMT_SRC_DIR . '/Additionals/SortUseNameSpace.php';
	require FMT_SRC_DIR . '/Additionals/SpaceAroundControlStructures.php';
	require FMT_SRC_DIR . '/Additionals/SpaceAfterExclamationMark.php';
	require FMT_SRC_DIR . '/Additionals/SpaceAroundExclamationMark.php';
	require FMT_SRC_DIR . '/Additionals/SpaceAroundParentheses.php';
	require FMT_SRC_DIR . '/Additionals/SpaceBetweenMethods.php';
	require FMT_SRC_DIR . '/Additionals/StrictBehavior.php';
	require FMT_SRC_DIR . '/Additionals/StrictComparison.php';
	require FMT_SRC_DIR . '/Additionals/StripExtraCommaInArray.php';
	require FMT_SRC_DIR . '/Additionals/StripNewlineAfterClassOpen.php';
	require FMT_SRC_DIR . '/Additionals/StripNewlineAfterCurlyOpen.php';
	require FMT_SRC_DIR . '/Additionals/StripNewlineWithinClassBody.php';
	require FMT_SRC_DIR . '/Additionals/StripSpaces.php';
	require FMT_SRC_DIR . '/Additionals/StripSpaceWithinControlStructures.php';
	require FMT_SRC_DIR . '/Additionals/TightConcat.php';
	require FMT_SRC_DIR . '/Additionals/TrimSpaceBeforeSemicolon.php';
	require FMT_SRC_DIR . '/Additionals/UpgradeToPreg.php';
	require FMT_SRC_DIR . '/Additionals/WordWrap.php';
	require FMT_SRC_DIR . '/Additionals/WrongConstructorName.php';
	require FMT_SRC_DIR . '/Additionals/YodaComparisons.php';

	if (!isset($inPhar)) {
		$inPhar = false;
	}
	if (!isset($testEnv)) {
		require FMT_SRC_DIR . '/cli-core.php';
	}
}
