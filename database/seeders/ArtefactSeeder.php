<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArtefactSeeder extends Seeder
{
    public function run(): void
    {
        // fid_rareté : 3 = 3 etoiles, 4 = 4 etoiles, 5 = 5 etoiles
        $artefacts = [
            // ═══════════════════════════════════════
            // 5★ — Sets endgame
            // ═══════════════════════════════════════
            [
                'nom' => 'A Day Carved From Rising Winds',
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'After a Normal Attack, Charged Attack, Elemental Skill, or Elemental Burst hits an opponent, gain the Blessing of Pastoral Winds effect for 6s: ATK is increased by 25%. If the equipping character has completed Witch\'s Homework, Blessing of Pastoral Winds will be upgraded to Resolve of Pastoral Winds, which also increases the CRIT Rate of the equipping character by an additional 20%. This effect can be triggered even when the character is off-field.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Aubade of Morningstar and Moon',
                'bonus_2p' => 'Increases Elemental Mastery by 80.',
                'bonus_4p' => 'When the equipping character is off-field, Lunar Reaction DMG is increased by 20%. When the party\'s Moonsign Level is at least Ascendant Gleam, Lunar Reaction DMG will be further increased by 40%. This effect will disappear after the equipping character is active for 3s.',
                'rarity' => 5,
            ],
            [
                'nom' => "Silken Moon's Serenade",
                'bonus_2p' => 'Energy Recharge +20%.',
                'bonus_4p' => 'When dealing Elemental DMG, gain the Gleaming Moon: Devotion effect for 8s: Increases all party members\' Elemental Mastery by 60/120 when the party\'s Moonsign is Nascent Gleam/Ascendant Gleam. The equipping character can trigger this effect while off-field. All party members\' Lunar Reaction DMG is increased by 10% for each different Gleaming Moon effect that party members have. Effects from Gleaming Moon cannot stack.',
                'rarity' => 5,
            ],
            [
                'nom' => "Night of the Sky's Unveiling",
                'bonus_2p' => 'Increases Elemental Mastery by 80.',
                'bonus_4p' => 'When nearby party members trigger Lunar Reactions, if the equipping character is on the field, gain the Gleaming Moon: Intent effect for 4s: Increases CRIT Rate by 15%/30% when the party\'s Moonsign is Nascent Gleam/Ascendant Gleam. All party members\' Lunar Reaction DMG is increased by 10% for each different Gleaming Moon effect that party members have. Effects from Gleaming Moon cannot stack.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Finale of the Deep Galleries',
                'bonus_2p' => 'Cryo DMG Bonus +15%.',
                'bonus_4p' => 'When the equipping character has 0 Elemental Energy, Normal Attack DMG is increased by 60% and Elemental Burst DMG is increased by 60%. After the equipping character deals Normal Attack DMG, the aforementioned Elemental Burst effect will stop applying for 6s. After the equipping character deals Elemental Burst DMG, the aforementioned Normal Attack effect will stop applying for 6s. This effect can trigger even if the equipping character is off the field.',
                'rarity' => 5,
            ],
            [
                'nom' => "Long Night's Oath",
                'bonus_2p' => 'Plunging Attack DMG increased by 25%.',
                'bonus_4p' => 'After the equipping character\'s Plunging Attack/Charged Attack/Elemental Skill hits an opponent, they will gain 1/2/2 stack(s) of "Radiance Everlasting." Plunging Attacks, Charged Attacks, or Elemental Skills can each trigger this effect once every 1s. Radiance Everlasting: Plunging Attacks deal 15% increased DMG for 6s. Max 5 stacks. Each stack\'s duration is counted independently.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Obsidian Codex',
                'bonus_2p' => 'While the equipping character is in Nightsoul\'s Blessing and is on the field, their DMG dealt is increased by 15%.',
                'bonus_4p' => 'After the equipping character consumes 1 Nightsoul point while on the field, CRIT Rate increases by 40% for 6s. This effect can trigger once every second.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Scroll of the Hero of Cinder City',
                'bonus_2p' => 'When a nearby party member triggers a Nightsoul Burst, the equipping character regenerates 6 Energy.',
                'bonus_4p' => 'After the equipping character triggers a reaction related to their Elemental Type, all nearby party members gain a 12% Elemental DMG Bonus for reaction-proximate Elemental Types for 15s. If the equipping character is in the Nightsoul\'s Blessing state when triggering this effect, all nearby party members gain an additional 28% Elemental DMG Bonus for reaction-proximate Elemental Types for 20s. The equipping character can trigger this effect while off-field, and the DMG bonus from Artifact Sets with the same name do not stack.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Unfinished Reverie',
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'After leaving combat for 3s, DMG dealt increased by 50%. In combat, if no Burning opponents are nearby for more than 6s, this DMG Bonus will decrease by 10% per second until it reaches 0%. When a Burning opponent exists, it will increase by 10% instead until it reaches 50%. This effect still triggers if the equipping character is off-field.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Fragment of Harmonic Whimsy',
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'When the value of a Bond of Life increases or decreases, this character deals 18% increased DMG for 6s. Max 3 stacks.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Song of Days Past',
                'bonus_2p' => 'Healing Bonus +15%.',
                'bonus_4p' => 'When the equipping character heals a party member, the Yearning effect will be created for 6s, which records the total amount of healing provided (including overflow healing). When the duration expires, the Yearning effect will be transformed into the "Waves of Days Past" effect: When your active party member hits an opponent with a Normal Attack, Charged Attack, Plunging Attack, Elemental Skill, or Elemental Burst, the DMG dealt will be increased by 8% of the total healing amount recorded by the Yearning effect. The "Waves of Days Past" effect is removed after it has taken effect 5 times or after 10s. A single instance of the Yearning effect can record up to 15,000 healing, and only a single instance can exist at once.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Nighttime Whispers in the Echoing Woods',
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'After using an Elemental Skill, gain a 20% Geo DMG Bonus for 10s. When under a shield granted by the Crystallize reaction, or when Moondrifts formed by Lunar-Crystallize reactions are nearby, the above effect is increased by 150%. When these conditions are no longer met, this additional increase disappears after 1s.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Golden Troupe',
                'bonus_2p' => 'Increases Elemental Skill DMG by 20%.',
                'bonus_4p' => 'Increases Elemental Skill DMG by 25%. Additionally, when not on the field, Elemental Skill DMG will be further increased by 25%. This effect will be cleared 2s after taking the field.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Marechaussee Hunter',
                'bonus_2p' => 'Normal and Charged Attack DMG +15%.',
                'bonus_4p' => 'When current HP increases or decreases, CRIT Rate will be increased by 12% for 5s. Max 3 stacks.',
                'rarity' => 5,
            ],
            [
                'nom' => "Vourukasha's Glow",
                'bonus_2p' => 'HP +20%.',
                'bonus_4p' => 'Elemental Skill and Elemental Burst DMG will be increased by 10%. After the equipping character takes DMG, the aforementioned DMG Bonus is increased by 80% for 5s. This effect increase can have 5 stacks. The duration of each stack is counted independently. These effects can be triggered even when the equipping character is not on the field.',
                'rarity' => 5,
            ],
            [
                'nom' => "Nymph's Dream",
                'bonus_2p' => 'Hydro DMG Bonus +15%.',
                'bonus_4p' => 'After Normal, Charged, and Plunging Attacks, Elemental Skills, and Elemental Bursts hit opponents, 1 stack of Mirrored Nymph will be triggered, lasting 8s. When under the effect of 1, 2, or 3 or more Mirrored Nymph stacks, ATK will be increased by 7%/16%/25%, and Hydro DMG Bonus will be increased by 4%/9%/15%. Mirrored Nymph stacks created by Normal, Charged, and Plunging Attacks, Elemental Skills, and Elemental Bursts exist independently.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Desert Pavilion Chronicle',
                'bonus_2p' => 'Anemo DMG Bonus +15%.',
                'bonus_4p' => 'When Charged Attacks hit opponents, the equipping character\'s Normal Attack SPD will increase by 10% while Normal, Charged, and Plunging Attack DMG will increase by 40% for 15s.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Flower of Paradise Lost',
                'bonus_2p' => 'Increases Elemental Mastery by 80.',
                'bonus_4p' => 'The equipping character\'s Bloom, Hyperbloom, and Burgeon reaction DMG are increased by 40%. Additionally, after the equipping character triggers Bloom, Hyperbloom, or Burgeon, they will gain another 25% bonus to the effect mentioned prior. Each stack of this lasts 10s. Max 4 stacks simultaneously. This effect can only be triggered once per second. The character who equips this can still trigger its effects when not on the field.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Gilded Dreams',
                'bonus_2p' => 'Elemental Mastery +80.',
                'bonus_4p' => 'Within 8s of triggering an Elemental Reaction, the character equipping this will obtain buffs based on the Elemental Type of the other party members. ATK is increased by 14% for each party member whose Elemental Type is the same as the equipping character, and Elemental Mastery is increased by 50 for every party member with a different Elemental Type. Each of the aforementioned buffs will count up to 3 characters. This effect can be triggered once every 8s. The character who equips this can still trigger its effects when not on the field.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Deepwood Memories',
                'bonus_2p' => 'Dendro DMG Bonus +15%.',
                'bonus_4p' => 'After Elemental Skills or Bursts hit opponents, the targets\' Dendro RES will be decreased by 30% for 8s. This effect can be triggered even if the equipping character is not on the field.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Retracing Bolide',
                'bonus_2p' => 'Increases Shield Strength by 35%.',
                'bonus_4p' => 'While protected by a shield, gain an additional 40% Normal and Charged Attack DMG.',
                'rarity' => 5,
            ],
            [
                'nom' => "Shimenawa's Reminiscence",
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'When casting an Elemental Skill, if the character has 15 or more Energy, they lose 15 Energy and Normal/Charged/Plunging Attack DMG is increased by 50% for 10s. This effect will not trigger again during that duration.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Vermillion Hereafter',
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'After using an Elemental Burst, this character will gain the Nascent Light effect, increasing their ATK by 8% for 16s. When the character\'s HP decreases, their ATK will further increase by 10%. This further increase can occur this way a maximum of 4 times. This effect can be triggered once every 0.8s. Nascent Light will be dispelled when the character leaves the field. If an Elemental Burst is used again during the duration of Nascent Light, the original Nascent Light will be dispelled.',
                'rarity' => 5,
            ],
            [
                'nom' => "Gladiator's Finale",
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'If the wielder of this artifact set uses a Sword, Claymore or Polearm, increases their Normal Attack DMG by 35%.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Maiden Beloved',
                'bonus_2p' => 'Character Healing Effectiveness +15%.',
                'bonus_4p' => 'Using an Elemental Skill or Burst increases healing received by all party members by 20% for 10s.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Pale Flame',
                'bonus_2p' => 'Physical DMG is increased by 25%.',
                'bonus_4p' => 'When an Elemental Skill hits an opponent, ATK is increased by 9% for 7s. This effect stacks up to 2 times and can be triggered once every 0.3s. Once 2 stacks are reached, the 2-set effect is increased by 100%.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Viridescent Venerer',
                'bonus_2p' => 'Anemo DMG Bonus +15%.',
                'bonus_4p' => 'Increases Swirl DMG by 60%. Decreases opponent\'s Elemental RES to the element infused in the Swirl by 40% for 10s.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Emblem of Severed Fate',
                'bonus_2p' => 'Energy Recharge +20%.',
                'bonus_4p' => 'Increases Elemental Burst DMG by 25% of Energy Recharge. A maximum of 75% bonus DMG can be obtained in this way.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Crimson Witch of Flames',
                'bonus_2p' => 'Pyro DMG Bonus +15%.',
                'bonus_4p' => 'Increases Overloaded and Burning DMG by 40%. Increases Vaporize and Melt DMG by 15%. Using Elemental Skill increases the 2-Piece Set Bonus by 50% of its starting value for 10s. Max 3 stacks.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Lavawalker',
                'bonus_2p' => 'Pyro RES increased by 40%.',
                'bonus_4p' => 'Increases DMG against opponents affected by Pyro by 35%.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Ocean-Hued Clam',
                'bonus_2p' => 'Healing Bonus +15%.',
                'bonus_4p' => 'When the character equipping this artifact set heals a character in the party, a Sea-Dyed Foam will appear for 3 seconds, accumulating the amount of HP recovered from healing (including overflow healing). At the end of the duration, the Sea-Dyed Foam will explode, dealing DMG to nearby opponents based on 90% of the accumulated healing. Only one Sea-Dyed Foam can be produced every 3.5 seconds. Each Sea-Dyed Foam can accumulate up to 30,000 HP (including overflow healing). There can be no more than one Sea-Dyed Foam active at any given time. This effect can still be triggered even when the character who is using this artifact set is not on the field.',
                'rarity' => 5,
            ],
            [
                'nom' => "Wanderer's Troupe",
                'bonus_2p' => 'Increases Elemental Mastery by 80.',
                'bonus_4p' => 'Increases Charged Attack DMG by 35% if the character uses a Catalyst or a Bow.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Heart of Depth',
                'bonus_2p' => 'Hydro DMG Bonus +15%.',
                'bonus_4p' => 'After using Elemental Skill, increases Normal Attack and Charged Attack DMG by 30% for 15s.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Bloodstained Chivalry',
                'bonus_2p' => 'Physical DMG +25%.',
                'bonus_4p' => 'After defeating an opponent, increases Charged Attack DMG by 50%, and reduces its Stamina cost to 0 for 10s.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Echoes of an Offering',
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'When Normal Attacks hit opponents, there is a 36% chance that it will trigger Valley Rite, which will increase Normal Attack DMG by 70% of ATK. This effect will be dispelled 0.05s after a Normal Attack deals DMG. If a Normal Attack fails to trigger Valley Rite, the odds of it triggering the next time will increase by 20%. This trigger can occur once every 0.2s.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Noblesse Oblige',
                'bonus_2p' => 'Elemental Burst DMG +20%.',
                'bonus_4p' => "Using an Elemental Burst increases all party members' ATK by 20% for 12s. This effect cannot stack.",
                'rarity' => 5,
            ],
            [
                'nom' => 'Archaic Petra',
                'bonus_2p' => 'Gain a 15% Geo DMG Bonus.',
                'bonus_4p' => 'Upon obtaining an Elemental Shard created through Crystallize or triggering a Lunar-Crystallize reaction, all party members gain a 35% DMG Bonus for that particular element for 10s. Only one form of Elemental DMG Bonus can be gained in this manner at any one time.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Thundersoother',
                'bonus_2p' => 'Electro RES increased by 40%.',
                'bonus_4p' => 'Increases DMG against opponents affected by Electro by 35%.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Thundering Fury',
                'bonus_2p' => 'Electro DMG Bonus +15%.',
                'bonus_4p' => 'Increases the DMG caused by Overloaded, Electro-Charged, Superconduct, and Hyperbloom by 40%, the DMG Bonus conferred by Aggravate by 20%. When Quicken or the aforementioned Elemental Reactions are triggered, Elemental Skill CD is decreased by 1s. Can only occur once every 0.8s.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Husk of Opulent Dreams',
                'bonus_2p' => 'DEF +30%.',
                'bonus_4p' => 'A character equipped with this Artifact set will obtain the Curiosity effect in the following conditions: When on the field, the character gains 1 stack after hitting an opponent with a Geo attack, triggering a maximum of once every 0.3s. When off the field, the character gains 1 stack every 3s. Curiosity can stack up to 4 times, each providing 6% DEF and a 6% Geo DMG Bonus. When 6 seconds pass without gaining a Curiosity stack, 1 stack is lost.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Tenacity of the Millelith',
                'bonus_2p' => 'HP increased by 20%.',
                'bonus_4p' => 'When an Elemental Skill hits an opponent, the ATK of all nearby party members is increased by 20% and their Shield Strength is increased by 30% for 3s. This effect can be triggered once every 0.5s. This effect can still be triggered even when the character who is using this artifact set is not on the field.',
                'rarity' => 5,
            ],
            [
                'nom' => 'Blizzard Strayer',
                'bonus_2p' => 'Cryo DMG Bonus +15%.',
                'bonus_4p' => 'When a character attacks an opponent affected by Cryo, their CRIT Rate is increased by 20%. If the opponent is Frozen, CRIT Rate is increased by an additional 20%.',
                'rarity' => 5,
            ],
            // ═══════════════════════════════════════
            // 4★ — Sets intermédiaires
            // ═══════════════════════════════════════
            [
                'nom' => 'Berserker',
                'bonus_2p' => 'CRIT Rate +12%.',
                'bonus_4p' => 'When HP is below 70%, CRIT Rate increases by an additional 24%.',
                'rarity' => 4,
            ],
            [
                'nom' => 'Instructor',
                'bonus_2p' => 'Increases Elemental Mastery by 80.',
                'bonus_4p' => "Upon triggering an Elemental Reaction, increases all party members' Elemental Mastery by 120 for 8s.",
                'rarity' => 4,
            ],
            [
                'nom' => 'The Exile',
                'bonus_2p' => 'Energy Recharge +20%.',
                'bonus_4p' => 'Using an Elemental Burst regenerates 2 Energy for all party members (excluding the wearer) every 2s for 6s. This effect cannot stack.',
                'rarity' => 4,
            ],
            [
                'nom' => 'Brave Heart',
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'Increases DMG by 30% against opponents with more than 50% HP.',
                'rarity' => 4,
            ],
            [
                "nom" => "Defender's Will",
                'bonus_2p' => 'DEF +30%.',
                'bonus_4p' => "For each different element present in your own party, the wearer's Elemental RES to that corresponding element is increased by 30%.",
                'rarity' => 4,
            ],
            [
                'nom' => 'Scholar',
                'bonus_2p' => 'Energy Recharge +20%.',
                'bonus_4p' => 'Gaining Elemental Particles or Orbs gives 3 Energy to all party members who have a bow or a catalyst equipped. Can only occur once every 3s.',
                'rarity' => 4,
            ],
            [
                'nom' => 'Tiny Miracle',
                'bonus_2p' => 'All Elemental RES increased by 20%.',
                'bonus_4p' => 'Incoming elemental DMG increases corresponding Elemental RES by 30% for 10s. Can only occur once every 10s.',
                'rarity' => 4,
            ],
            [
                'nom' => 'Martial Artist',
                'bonus_2p' => 'Increases Normal Attack and Charged Attack DMG by 15%.',
                'bonus_4p' => 'After using Elemental Skill, increases Normal Attack and Charged Attack DMG by 25% for 8s.',
                'rarity' => 4,
            ],
            [
                'nom' => 'Resolution of Sojourner',
                'bonus_2p' => 'ATK +18%.',
                'bonus_4p' => 'Increases Charged Attack CRIT Rate by 30%.',
                'rarity' => 4,
            ],
            [
                'nom' => 'Gambler',
                'bonus_2p' => 'Increases Elemental Skill DMG by 20%.',
                'bonus_4p' => 'Defeating an opponent has a 100% chance to remove Elemental Skill CD. Can only occur once every 15s.',
                'rarity' => 4,
            ],
            // ═══════════════════════════════════════
            // 3★ — Sets débutants
            // ═══════════════════════════════════════
            [
                'nom' => 'Adventurer',
                'bonus_2p' => 'Max HP increased by 1000.',
                'bonus_4p' => 'Opening a chest regenerates 30% Max HP over 5s.',
                'rarity' => 3,
            ],
            [
                'nom' => 'Lucky Dog',
                'bonus_2p' => 'DEF increased by 100.',
                'bonus_4p' => 'Picking up Mora restores 300 HP.',
                'rarity' => 3,
            ],
            [
                'nom' => 'Traveling Doctor',
                'bonus_2p' => 'Increases incoming healing by 20%.',
                'bonus_4p' => 'Using Elemental Burst restores 20% HP.',
                'rarity' => 3,
            ],
            [
                'nom' => 'Prayers to Springtime',
                'bonus_2p' => null,
                'bonus_4p' => null,
                'rarity' => 3,
            ],
            [
                'nom' => 'Prayers for Destiny',
                'bonus_2p' => null,
                'bonus_4p' => null,
                'rarity' => 3,
            ],
            [
                'nom' => 'Prayers for Illumination',
                'bonus_2p' => null,
                'bonus_4p' => null,
                'rarity' => 3,
            ],
            [
                'nom' => 'Prayers for Wisdom',
                'bonus_2p' => null,
                'bonus_4p' => null,
                'rarity' => 3,
            ],
        ];

        foreach ($artefacts as $a) {
            DB::table('artefact')->insertOrIgnore([
                'nom_artefact' => $a['nom'],
                'slug'         => Str::slug($a['nom']),
                'bonus_2p'     => $a['bonus_2p'],
                'bonus_4p'     => $a['bonus_4p'],
                'fid_rareté'   => $a['rarity'],
            ]);
        }
    }
}
