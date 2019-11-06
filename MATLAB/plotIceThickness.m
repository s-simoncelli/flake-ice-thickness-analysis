clc; clear; close all
% Test Stefan's Law for one point of the air temperature grid

load('/Volumes/PTV #2/rda/ice_2019/out/iceThickness.mat');

P = [53.15283 13.02655];

[k, dist] = dsearchn([latidueGrid(:) longitudeGrid(:)], P);

[d1, d2, ~] = size(airTSeries);
[row, col] = ind2sub([d1 d2], k) ;
T = squeeze(airTSeries(row, col, :)); 
h = squeeze(iceThickness(row, col, :)); 

%% Plot
figure; 
yyaxis left; hold on;
plot(newTimeVector, T, 'k.-');
plot(get(gca, 'XLim'), [0 0], 'b-', 'LineWidth', 2);
axis tight;

yyaxis right;
plot(newTimeVector, h*100, 'r.-');
axis tight;