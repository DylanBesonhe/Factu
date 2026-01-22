# Setup BMAD Method - Projet Factu

## Date de configuration
15 janvier 2026

## Ce qui a ete fait

### 1. Creation du projet
- Dossier cree : `D:/ClaudeProjects/Factu`

### 2. Installation de BMAD Method
Source : https://github.com/bmad-code-org/BMAD-METHOD (v6.0.0-alpha.23)

Installation manuelle effectuee car l'installeur npx est interactif.

### 3. Structure installee

```
Factu/
├── .claude/
│   └── agents/                    # 16 sub-agents pour Claude Code
│       ├── bmad-analysis/
│       │   ├── api-documenter.md
│       │   ├── codebase-analyzer.md
│       │   ├── data-analyst.md
│       │   └── pattern-detector.md
│       ├── bmad-planning/
│       │   ├── dependency-mapper.md
│       │   ├── epic-optimizer.md
│       │   ├── requirements-analyst.md
│       │   ├── technical-decisions-curator.md
│       │   ├── trend-spotter.md
│       │   ├── user-journey-mapper.md
│       │   └── user-researcher.md
│       ├── bmad-research/
│       │   ├── market-researcher.md
│       │   └── tech-debt-auditor.md
│       └── bmad-review/
│           ├── document-reviewer.md
│           ├── technical-evaluator.md
│           └── test-coverage-analyzer.md
├── _bmad/
│   ├── config.yaml                # Configuration projet
│   ├── core/                      # Module core BMAD
│   │   ├── agents/
│   │   │   └── bmad-master.agent.yaml
│   │   ├── tasks/
│   │   ├── workflows/
│   │   │   ├── advanced-elicitation/
│   │   │   ├── brainstorming/
│   │   │   └── party-mode/
│   │   └── resources/
│   └── bmm/                       # Module BMad Method
│       ├── agents/                # 9 agents principaux
│       │   ├── analyst.agent.yaml
│       │   ├── architect.agent.yaml
│       │   ├── dev.agent.yaml
│       │   ├── pm.agent.yaml
│       │   ├── quick-flow-solo-dev.agent.yaml
│       │   ├── sm.agent.yaml
│       │   ├── tea.agent.yaml
│       │   ├── tech-writer.agent.yaml
│       │   └── ux-designer.agent.yaml
│       ├── workflows/             # 34 workflows agiles
│       ├── teams/
│       ├── templates/
│       └── data/
├── _bmad-output/
│   ├── planning-artifacts/        # Briefs, PRDs, architectures
│   └── implementation-artifacts/  # Stories, sprints, reviews
└── docs/
    └── setup-bmad.md              # Ce fichier
```

### 4. Configuration (config.yaml)

```yaml
user_name: "Dylan"
communication_language: "French"
document_output_language: "French"
output_folder: "_bmad-output"
project_name: "Factu"
user_skill_level: "expert"
```

## Qu'est-ce que BMAD Method ?

**BMAD** = Build More, Architect Dreams

Framework de developpement agile pilote par l'IA avec :
- **21 agents specialises** : PM, Architecte, Dev, UX, Scrum Master, etc.
- **50+ workflows guides** sur 4 phases agiles
- **Intelligence adaptative** : ajuste la profondeur selon la complexite (Level 0-4)

### Les 3 modules disponibles

| Module | Description |
|--------|-------------|
| **BMM** (BMad Method) | Core agile - 34 workflows sur 4 phases |
| **BMB** (BMad Builder) | Creation d'agents et modules custom |
| **CIS** (Creative Intelligence Suite) | Innovation et brainstorming |

### Les 3 tracks de travail

| Track | Usage | Duree |
|-------|-------|-------|
| **Quick Flow** | Bug fixes, petites features | ~5 min |
| **BMad Method** | Produits et plateformes | ~15 min |
| **Enterprise** | Systemes avec compliance | ~30 min |

## Pour demarrer demain

1. **Initialiser le workflow** : `*workflow-init`
   - Analyse le projet et recommande un track

2. **Ou charger un agent directement** :
   - PM Agent : pour creer le Product Brief puis PRD
   - Architect : pour la conception technique
   - Dev : pour l'implementation

3. **Workflow typique BMad Method** :
   - Phase 1 : Analyse (Brief, Research)
   - Phase 2 : Planning (PRD, UX Design)
   - Phase 3 : Architecture (Tech Design, Epics)
   - Phase 4 : Implementation (Stories, Sprints, Code)

## Ressources

- Documentation officielle : http://docs.bmad-method.org/
- GitHub : https://github.com/bmad-code-org/BMAD-METHOD
- Discord communaute : https://discord.gg/gk8jAdXWmj

## Projet Factu - A definir

Le projet Factu n'a pas encore de specification.
Demain, utiliser le PM Agent pour :
1. Definir l'objectif du projet
2. Creer le Product Brief
3. Elaborer le PRD
