#!/usr/bin/perl -w

# 試合情報
# [ YY/MM/DD vs 相手チーム ]


# [イニング]-[打球位置]-[記録]-[打点]-[盗塁][*]
# イニング： 1-9
# 打球位置： [1-9][GFOBDLR] G=ゴロ F=フライ L=ライナー
#		O=オーバー B=四球 D=死球 I=打撃妨害 R=代走
# 	注：8G9は右中間への打球 7G8は左中間への打球を示す
# 記録： H[1-3R](ヒット,HRはホームラン),O(アウト),G(犠打),E(エラー)
# 打点： 0-4
# 盗塁： 0-3
# 最後の"*"は本塁への帰還(得点)
#
# 勝ち投手 先発半分以上の回
# セーブ ３点以内から最後まで投げる or ２イニング以上

use strict;
use XML::Simple;
use Data::Dumper;
use Encode;

binmode STDOUT, ":encoding(utf8)";
binmode STDERR, ":encoding(utf8)";

my $debug = 0;

sub outputxml($) {
    my $data = shift;
    my $x = new XML::Simple;
    my $xml = $x->XMLout($data, RootName=>"monstars");
    print "<?xml version=\"1.0\" encoding=\"utf8\" ?>\n";
    print $xml;
}

my @games = ();

sub score {
    my $line = shift;
    $line =~ s/^\s*(\S+)\s*\|/$1 /;
    $line =~ s/\|/ \| /;
    $line =~ s/^\s+//;
    $line =~ s/\s+/ /g;
    my @score = split /\s+/, $line;
}
sub scoreboard {
    my ($batFirst, $fieldFirst) = @_;
    my @bat = score($batFirst);
    my @field = score($fieldFirst);
    my $scoreboard;
    my $teamBatFirst = shift @bat;
    my $teamFieldFirst = shift @field;
    my $monstarsName = decode("euc-jp", '^(モンスターズ|MonStars|M|Mon|MS|Monstars)$');
    if ($teamBatFirst =~ /$monstarsName/) { $teamBatFirst = "MonStars"; }
    if ($teamFieldFirst =~ /$monstarsName/) { $teamFieldFirst = "MonStars"; }

    $scoreboard->{team} = { 'batFirst' => $teamBatFirst, 'fieldFirst' => $teamFieldFirst };

    my @point = ();
    my ($totalBatFirst, $totalFieldFirst) = (0, 0);
    my $i = 1;
    foreach my $sbat (@bat) {
        last if ($sbat eq "|" or $sbat =~ /^\(.*\)$/);
        my $sfield = shift @field;
        $sfield =~ s/X/x/;
        my $inning = {inning => $i, batFirst => $sbat, fieldFirst => $sfield };
        unless ($sbat =~ /\(.*\)/) {
            $totalBatFirst += $sbat;
            $sfield =~ s/[xX\|-]//;
            unless ($sfield =~ /\(.*\)/ or $sfield eq "") {
                $totalFieldFirst += $sfield;
            }
        }
        push @point, $inning;
    } continue {
        $i++;
    }
    my $total = { inning => "total", batFirst => $totalBatFirst, fieldFirst => $totalFieldFirst };
    push @point, $total;
    $scoreboard->{score} = \@point;

    my $result;
    if ($teamBatFirst =~ /$monstarsName/) {
        $scoreboard->{monstars} = "batFirst";
        if ($totalBatFirst > $totalFieldFirst) {
            $result = 1; #won
        } elsif ($totalBatFirst == $totalFieldFirst) {
            $result = 0; # draw
        } else {
            $result = -1; #lost
        }
    } elsif ($teamFieldFirst =~ /$monstarsName/) {
        $scoreboard->{monstars} = "fieldFirst";
        if ($totalBatFirst > $totalFieldFirst) {
            $result = -1;
        } elsif ($totalBatFirst == $totalFieldFirst) {
            $result = 0;
        } else {
            $result = 1;
        }
    } else {
        print STDERR "Warning: No Monstars ($teamBatFirst vs $teamFieldFirst) line $.\n";
        $result = "internal";
    }

    return ($result, $scoreboard);
}

sub xmlgen($) {
    my $in_file = shift;
    #my $begin = '^\s*(?:<H1>)?\[\s*((?:[0-9]+\/)?[0-9]+\/[0-9]+)(?:\s+v\.?s\.?)?\s+(.+)\s*\].*$';
    my $begin = '^\s*(?:<H1>)?\[\s*((?:[0-9]+\/)?[0-9]+\/[0-9]+)(?:\s+v\.?s\.?)?\s+(\S+)\s*\].*$';
    my @scoreBatFirst = ();
    open IN, '<:encoding(shiftjis)', $in_file;
    while (<IN>) {
        last if not defined $_;
        s/\x0D\x0A|\x0D|\x0A/\n/g;
        next if ($_ =~ /^\s*$/); # blank line
#        next if ($_ =~ /^\s*#/); # comment line
        next if ($_ =~ /^\t/);
        if ($_ =~ '^\s*(?:<H1>)?\[\s*([0-9]+\/[0-9]+)(?:\s+v\.?s\.?)?\s+(.+)\s*\].*$') {
            print STDERR "Warning: Wrong game information --> $_";
        }
        if ($_ =~ /$begin/) {
            # a game
            print STDERR $_ if $debug;
            my $game;
            $game->{date} = $1 if defined $1;
            $game->{team} = $2 if defined $2;
            $in_file =~ /^.*\/([0-9]+)\.(txt|htm)$/;
            $game->{season} = $1;
            my @batters = ();
            my $comment;
            my $order = 0;
            while (<IN>) {
                s/\x0D\x0A|\x0D|\x0A/\n/g;
                my $wSpace = decode("euc-jp", '　');
                s/$wSpace/ /g;
                my $wPipe = decode("euc-jp", '｜');
                s/$wPipe/\|/g;
                if ($_ =~ /$begin/) {
                    last;
                } elsif ($_ =~ /^\s+\S+\s+([0-9]+:[0-9]+).*[0-9]+:[0-9]+\s*([\S]+)(\s+([\S]+))*/) {
                    # /月日 時間 場所 試合種別
                    # game information
                    $game->{time} = $1 if defined $1;
                    $game->{loc} = $2 if defined $2;
                    $game->{game} = $4 if defined $4;
                } elsif ($_ =~ /^\s*\S+(?:\s+[\|\(\)0-9xX-]+)+\s*$/) {
                    # scoreboard
                    my $nextline = <IN>;
                    ($game->{result}, $game->{scoreboard}) = scoreboard($_, $nextline);
                } elsif ($_ =~ /^\s#?(.*)$/ or $_ =~ /\s*#(.+)/) {
                    # comment
                    $comment .= $1 . "<br>";
                } elsif ($_ =~ /^([0-9>@\?]+)([\w-]+)\s+(.*)/) {
                    my ($position, $name) = ($1, $2);
                    my @line = split;
                    shift @line;
                    my @battings;
                    foreach my $line (@line) {
                        unless ($line =~ /^[0-9]+-([BKDIR]|[1-9\?]?[GFLO\?]?)-?([OHEG]?[0-3R]?)-?([0-4]?)-?([0-3]?)(\*)?(\/\/)?$/) {
                            print STDERR "Invalid data $line at line $.\n";
                        }
                        my $batting;
                        $batting->{raw} = $line;
                        push @battings, $batting;
                    }
                    if ($position ne '>') {
                        $order ++;
                    }
                    my $batter = { name => $name, position => $position, order => $order, batting => \@battings };
                    push @batters, $batter;
                }
            }
            if (defined $game) {
                $game->{batters}->{batter} = \@batters if ($#batters > 0);
                if (defined $comment) {
                    $game->{comment} = $comment;
                    my $wonWithoutGame = decode("euc-jp", '不戦勝');
                    my $lostWithoutGame = decode("euc-jp", '不戦敗');
                    if ($comment =~ /$wonWithoutGame/) {
                        $game->{result} = $wonWithoutGame;
                    } elsif ($comment =~ /$lostWithoutGame/) {
                        $game->{result} = $lostWithoutGame;
                    }
                }
                push @games, $game;
            }
            redo;
            # end of a game
        } elsif ($_ =~ /^ .*$/ or $_ =~ /^\s*#/) {
            # comment
        } else {
            die "UNKNOWN $_ at $.\n";
        }
    }

    close (IN);
}

sub xmldec($) {
    my $in_file = shift;
    open IN, '<:encoding(shiftjis)', $in_file;
    my $in;
    while (<IN>) {
        $in .= $_;
    }
     close IN;
    my $xml = XMLin($in);
    print Dumper($xml);
    my $games = $xml->{game};
    for my $game (@$games) {
        print "$game->{date}";
        print " $game->{time}" if defined $game->{time};
        print " $game->{loc}" if defined $game->{loc};
        print "\n";
        print "\t$game->{game}" if defined $game->{game};
        print "\tvs $game->{team}";
        print "\n";
        print "$game->{comment}\n" if defined $game->{comment};
        my $scoreboard = $game->{scoreboard};
        if (defined $scoreboard) {
            my $scores = $scoreboard->{score};
            printf "%20s", $scoreboard->{team}->{batFirst};
            for my $score (@$scores) {
                if ($score->{inning} eq "total") {
                    print " | ";
                }
                print " $score->{batFirst}";
            }
            print "\n";
            printf "%20s", $scoreboard->{team}->{fieldFirst};
            for my $score (@$scores) {
                if ($score->{inning} eq "total") {
                    print " | ";
                }
                print " $score->{fieldFirst}";
            }
            print "\n";
        }
        my $batters = $game->{batters};
        for my $batter (@$batters) {
            print "$batter->{batter}\n";
        }
        print "\n";
    }
}

### Main

my $reverse = 0;

foreach (@ARGV) {

    if ($_ =~ /^(-r|-R)$/) {
        $reverse++;
        next;
    }

    if ($reverse) {
        xmldec($_);
    } else {
        xmlgen($_);
    }
}
if ($reverse == 0) {
    #    my $all->{game} = \@games;
    my $all;
    $all->{game} = \@games;
    outputxml($all);
    print Dumper($all) if $debug;
}
